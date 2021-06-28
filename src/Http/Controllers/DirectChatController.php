<?php

namespace Codificar\Chat\Http\Controllers;

use Grimzy\LaravelMysqlSpatial\Types\Point;
use App\Http\Controllers\Controller;
use Codificar\Chat\Events\EventConversation;
use Codificar\Chat\Events\EventNotifyPanel;
use Codificar\Chat\Http\Requests\GetDirectRequest;
use Codificar\Chat\Http\Requests\ListDirectConversationRequest;
use Codificar\Chat\Http\Requests\SendDirectRequest;
use Codificar\Chat\Http\Resources\ChatMessagesResource;
use Codificar\Chat\Http\Resources\ListDirectConversationCollection;
use Codificar\Chat\Http\Resources\ListDirectConversationResource;
use Codificar\Chat\Http\Resources\ListProvidersForConversation;
use DB;
use Ledger;
use Nahid\Talk\Conversations\Conversation;
use Provider;
use Settings;
use Admin, Auth;
use Codificar\Chat\Http\Resources\FilterConversationsResource;
use Codificar\Chat\Http\Resources\ListConversationsForPanelResource;
use Codificar\Chat\Http\Utils\Helper;
use Location;

class DirectChatController extends Controller
{
    /**
     * Render chat screen
     * @return view
     */
    public function renderDirectChat($id = null)
    {
        $user = Auth::guard('web_corp')->user();
        $ledger = null;

        if (!$user || !$user->AdminInstitution) {
            $user = Auth::guard('web_corp')->user();

            if (!$user)
                return \Redirect::to("/corp/login");
        }

        $ledger = Helper::getLedger(
            'corp', 
            $user->AdminInstitution->Institution->default_user_id
        );
        
        return view('chat::direct_chat', [
            'environment' => 'corp',
            'user' => $user,
            'ledger_id' => $ledger ? $ledger->id : null,
            'user_id' => $id,
            'new_conversation' => null,
            'conversation_id' => $id
        ]);
    }

    /**
     * @api {POST} /api/libs/set_direct_message
     * Send direct message
     * @param SendDirectRequest $request
     * @return json
     */
    public function sendDirectMessage(SendDirectRequest $request)
    {
        $conversation = $this->geOrCreatetConversation($request);
        $messageText = $request->message ? $request->message : date('d/m/Y');
        
        \Talk::setAuthUserId($request->sender_id);
        $message = \Talk::sendMessage($conversation->id, $messageText);
        Helper::savePicture($request, $message);

        event(new EventConversation($message->id));

        if ($request->ledger_receiver->admin_id)
            event(new EventNotifyPanel($request->receiver_id));

        if ($request->ledger_receiver->user_id) {
            
            // notifica user
            $this->sendNotificationMessageReceived(
                trans('laravelchat::laravelchat.new_message'), 
                $message->conversation_id, 
                $message->message, 
                $request->ledger_receiver->user_id, 
                'user'
            );
        } else if ($request->ledger_receiver->provider_id) {
            // notifica provider
            $this->sendNotificationMessageReceived(
                trans('laravelchat::laravelchat.new_message'), 
                $message->conversation_id, 
                $message->message, 
                $request->ledger_receiver->provider_id, 
                'provider'
            );
        }

        return response()->json([
            "success" => true, 
            "id" => $request->receiver_id,
            "conversation_id" => $message->conversation_id,
            "receiver_name" => $request->receiver_name,
            "receiver_picture" => $request->receiver_picture,
            "message" => $message
        ]);
    }

    /**
	 * Envia push quando há uma nova mensagem ou proposta
	 * 
	 * @return
	 */
	public function sendNotificationMessageReceived($title, $conversation_id, $contents, $model_id, $type) {
		try {
			// Send Notification
			$message = array(
				'success' => true,
				'conversation_id' => $conversation_id,
				'message' => $contents
			);
			//envia notificação push
			send_notifications($model_id, $type, $title, $message);
		} catch (\Exception $ex) {
			return $ex->getMessage().$ex->getTraceAsString();
		}
	}

    /**
     * @api {GET} /api/libs/get_direct_message
     * Retrive direct messages in conversation
     * @param object $request
     * @return Conversation
     */
    public function getDirectMessages(GetDirectRequest $request)
    {
        $conversation = $this->getConversationBySender($request);

        return new ChatMessagesResource([
			"messages" => $conversation ? $conversation->messages : [],
			"request_id" => 0,
			"user_id" => $request->sender_id
		]);
    }

    /**
     * @api {GET} /api/libs/list_direct_conversation
     * List all direct conversation
     * @param ListDirectConversationRequest $request
     * @return ListDirectConversationResource
     */
    public function listDirectConversations(ListDirectConversationRequest $request)
    {
        $conversations = null;
        $locations = Location::select('id', 'name')->get();

        if ($request->sender_type == 'user' || $request->sender_type == 'provider') {

            $conversations = Conversation::whereRaw("request_id = 0 and (user_one = $request->sender_id or user_two = $request->sender_id)")
                ->with(['messages'])
                ->orderBy('updated_at', 'desc')
                ->get();
            
            return new ListConversationsForPanelResource([
                'sender_id' => $request->sender_id,
                'sender_type' => $request->sender_type,
                'conversations' => $conversations,
                'locations' => $locations
            ]);
        } else if ($request->sender_type == 'corp') {

            $conversations = Conversation::where('user_one', $request->sender_id)
                ->orWhere('user_two', $request->sender_id)
                ->with(['messages'])
                ->orderBy('updated_at', 'desc')
                ->get();
            
            return new ListConversationsForPanelResource([
                'sender_id' => $request->sender_id,
                'sender_type' => $request->sender_type,
                'conversations' => $conversations,
                'locations' => $locations
            ]);
        }
        
        return new ListDirectConversationResource([
            'sender_type' => $request->sender_type,
            'conversations' => $conversations,
            'locations' => $locations
        ]);
    }

    /**
     * Get paginated conversations
     * 
     * @param ListDirectConversationRequest $request
     * @return FilterConversationsResource
     */
    public function filterConversations(ListDirectConversationRequest $request)
    {
        $conversations = Helper::filterDirectFetch($request);
        $locations = Location::select('id', 'name')->get();
        
        return new FilterConversationsResource([
            'sender_id' => $request->sender_id,
            'sender_type' => $request->sender_type,
            'conversations' => $conversations['data'],
            'last_page' => $conversations['last_page'],
            'current_page' => $conversations['current_page'],
            'locations' => $locations
        ]);
    }

    /**
     * Retrieve or create new conversation
     * @param object $request
     * @return Conversation
     */
    private function geOrCreatetConversation($request)
    {
        $conversation = $this->getConversationBySender($request);

        if ($conversation)
            return $conversation;

        $conversation = new Conversation();
        $conversation->user_one = $request->sender_id;
        $conversation->user_two = $request->receiver_id;
        $conversation->request_id = 0;
        $conversation->status = 1;
        $conversation->save();

        return $conversation;
    }

    /**
     * Retrive a conversation by sender
     * @param object $request
     * @return Conversation
     */
    private function getConversationBySender($request)
    {
        try {
            $conversation = null;
    
            $conversation = Conversation::whereRaw("request_id = 0 and ((user_one = $request->sender_id and user_two = $request->receiver_id) or (user_one = $request->receiver_id and user_two = $request->sender_id))")
                ->with(['messages'])
                ->orderBy('updated_at', 'desc')
                ->first();
    
            return $conversation;
        } catch (\Throwable $th) {
            return null;
        }
    }

    /**
     * @api {GET} /api/libs/get_providers_chat
     * List providers to chat
     * @param ListDirectConversationRequest $request
     * @return json
     */
    public function getProvidersForConversation(ListDirectConversationRequest $request)
    {
        $user = $request->userSystem;

        $distanceSearchRadius = Settings::getDefaultSearchRadius();
        $unitMultiply         = Settings::getDefaultMultiplyUnit();
        $referencePoint = new Point($user->latitude, $user->longitude);

        if ($request->name) {
            $providers = $this->getProvidersByName($request->name);
        } else {
            $providers = $this->getProvidersWithinDistance(
                $referencePoint, 
                $distanceSearchRadius * $unitMultiply
            );
        }

        return response()->json([
            'providers' => ListProvidersForConversation::collection($providers)
        ]);
    }

    /**
	 * Get the providers within a radius from a reference point, sorted by distance (km).
	 * @param Point $referencePoint the reference coordinate
	 * @param int $radius in Kilometers
	 * 
	 * @return $query the builded query
	 */
    public function getProvidersWithinDistance($referencePoint, $radius)
    {
        $query = Provider::query();
        $query->selectRaw("id, first_name, last_name, picture, st_distance_sphere(GeomFromText(CONCAT('POINT(',`latitude`,' ',`longitude`,')')), ST_GeomFromText('{$referencePoint->toWkt()}'))/1000 as distance");
        $query->whereRaw("st_distance_sphere(GeomFromText(CONCAT('POINT(',`latitude`,' ',`longitude`,')')), ST_GeomFromText('{$referencePoint->toWkt()}')) <= {$radius}*1000");
        $query->orderBy('distance');

        return $query->limit(20)->get();
    }

    /**
	 * Get the providers by name.
	 * @param string $name
	 * 
	 * @return $query the builded query
	 */
    public function getProvidersByName($name)
    {
        $query = Provider::where(
            DB::raw('CONCAT_WS(" ", first_name, last_name)'), 
            'like', 
            '%' . $name . '%'
        )
            ->limit(20)
            ->get();

        return $query;
    }

}