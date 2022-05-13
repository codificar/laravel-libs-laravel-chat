<?php

namespace Codificar\Chat\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DeliveryPackage;
use App\Models\Institution;
use App\Models\RequestPoint;
use Codificar\Chat\Models\ConversationRequest;
use Codificar\Chat\Http\Requests\SendMessageRequest;
use Codificar\Chat\Http\Requests\ConversationFormRequest;
use Codificar\Chat\Http\Requests\MessageListFormRequest;
use Codificar\Chat\Http\Requests\MessageSeenFormRequest;
use Codificar\Chat\Http\Resources\ConversationsResource;
use Codificar\Chat\Http\Resources\ChatMessagesResource;
use Codificar\Chat\Events\EventConversation;
use Codificar\Chat\Events\EventNewConversation;
use Codificar\Chat\Events\EventNotifyPanel;
use Codificar\Chat\Events\EventReadMessage;
use Codificar\Chat\Http\Utils\Helper;
use Codificar\Chat\Models\DeliveryPackageRequest;
use Requests, Admin, Auth, User, Provider;
use Log;
use Nahid\Talk\Messages\Message;
use Settings;
use Nahid\Talk\Conversations\Conversation;
use Illuminate\Http\Request;

class RideChatController extends Controller
{
	/**
	 * Render admin chat page
	 * @return View
	 */
	public function adminRequestChat($request_id){
		$admin = Admin::find(Auth::guard('web')->user()->id);
		if (!$admin) {
			return \Redirect::to("/admin/home");
		}

		$request = Requests::find($request_id);

		//Esses dois campos estavam dando problema no json_encode e não são necessários no chat
		unset($request->origin);
		unset($request->destination);
		if (!$request){
			abort(404);
		}

		$requestPoints = RequestPoint::whereRequestId($request->id)->get();

		$user = User::getUserForChat($request->user_id);
		
		if(in_array($request->user_id, Institution::getDefaultUsersIds())){
			$institution = Institution::getByDefaultUserId($request->user_id);
		} else {
			$institution = null;
		}
		
		$mapsApiKey = Settings::getGoogleMapsApiKey();
		
		$viewData = [
			"environment" => "admin",
			"request" => $request,
			"requestPoints" => $requestPoints,
			"user" => $user,
			"userAdmin" => [
				'name' => $admin->profile->name,
				'image' => \Theme::getLogoUrl()
			],
			"institution" => $institution,
			"maps_api_key" => $mapsApiKey,
		];

		return view('chat::chat', $viewData);
	}

	/**
	 * Render user chat page
	 * @return View
	 */
	public function userRequestChat($request_id){
		
		$user_id = Auth::guard("clients")->user()->id;

		$request = Requests::find($request_id);

		if (!$request || ($request->user_id != $user_id)) {
			abort(404);
		}

		$user = User::getUserForChat($request->user_id);
		if (in_array($request->user_id, Institution::getDefaultUsersIds())) {
			$institution = Institution::getByDefaultUserId($request->user_id);
		} else {
			$institution = "";
		}

		$requestPoints = RequestPoint::whereRequestId($request->id)->get();

		$mapsApiKey = Settings::getGoogleMapsApiKey();

		$viewData = [
			"environment" => "user",
			"request" => $request,
			"requestPoints" => $requestPoints,
			"user" => $user,
			"institution" => $institution,
			"maps_api_key" => $mapsApiKey,
		];

		return view('chat::chat', $viewData);
	}

	/**
	 * Render provider chat page
	 * @return view
	 */
	public function providerRequestChat($request_id) {
		$provider = Provider::getProviderForChat([Auth::guard('providers')->user()->id]);

		$ride = Requests::find($request_id);

		//Esses dois campos estavam dando problema no json_encode e não são necessários no chat
		unset($ride->origin);
		unset($ride->destination);
		if (!$ride){
			abort(404);
		}

		$requestPoints = RequestPoint::whereRequestId($ride->id)->get();

		if(in_array($ride->user_id, Institution::getDefaultUsersIds())){
			$institution = Institution::getByDefaultUserId($ride->user_id);
		} else {
			$institution = "";
		}

		$mapsApiKey = Settings::getGoogleMapsApiKey();
		
		$viewData = [
			"environment" => "provider",
			"request" => $ride,
			"requestPoints" => $requestPoints,
			"user" => $provider,
			"institution" => $institution,
			"maps_api_key" => $mapsApiKey,
		];

		return view('chat::chat', $viewData);
	}

	/**
	 * Render corp chat page
	 * @return view
	 */
	public function corpRequestChat($request_id){
		
		$user = Auth::guard('web')->user();

		if (!$user || !$user->AdminInstitution) {
            $user = Auth::guard('web_corp')->user();

            if (!$user)
                return \Redirect::to("/corp/login");
        }

		$ledger = null;
		$ride = Requests::find($request_id);
		$provider = $ride->confirmedProvider;

		if (!$provider)
			abort(404);

        if ($user) {
            $ledger = Helper::getLedger(
                'corp', 
                $user->AdminInstitution->Institution->default_user_id
            );
		}
		
		$newConversation = [
			'full_name' => $provider->first_name . ' ' . $provider->last_name,
			'picture' => $provider->picture,
			'request_id' => $ride->id,
			'conversation_id' => 0,
			'last_message' => '',
			'time' => '',
			'messages' => []
		];

		$conversation = Conversation::where('request_id', $ride->id)->first();

		if ($conversation)
			$newConversation = null;
        
        return view('chat::direct_chat', [
            'environment' => 'corp',
            'user' => $user,
            'ledger_id' => $ledger ? $ledger->id : null,
			'request_id' => $ride ? $ride->id : null,
			'new_conversation' => $newConversation,
			'conversation_id' => $conversation ? $conversation->id : null
        ]);
	}
	
    /**
     * Send a new message on ride conversation
     * @api {POST} /api/libs/user/chat/send
     * @api {POST} /api/libs/provider/chat/send
     * @param SendMessageRequest $request
     * @return json
     */
    public function sendMessage(SendMessageRequest $request) {
		try {
			$convRequest = ConversationRequest::findConversation($request->request_id, $request->provider_id);

			$ride = Requests::find($request->request_id);
			$isNewConversation = $convRequest->conversation_id == 0;

			$message = $convRequest->sendMessage($request->receiver_id, $request->message);
			
			if ($isNewConversation) {
				event(new EventNewConversation($ride->id, $message->conversation_id, $request->receiver_id));
			}

			if ($request->is_admin) {
				$admin = Admin::find(Auth::guard('web')->user()->id);
				if ($admin) {
					$message->admin_id = $admin->id;
					$message->save();
				}
			}
			
            event(new EventConversation($message->id));
            
			if ($request->sender_type == 'provider') {
				event(new EventNotifyPanel($request->receiver_id));
				// notifica user
				$this->sendNotificationMessageReceived(trans('laravelchat::laravelchat.new_message'), $message->conversation_id, $message->message, $request->ledger_receiver->user_id, 'user');
			}
			else {
				// notifica provider
				$this->sendNotificationMessageReceived(trans('laravelchat::laravelchat.new_message'), $message->conversation_id, $message->message, $request->ledger_receiver->provider_id, 'provider');
			}

		} catch(\Exception $ex) {
			Log::error($ex->getMessage().$ex->getTraceAsString());
		}

		return response()->json([
            "success" => true, 
			"conversation_id" => $message->conversation_id,
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
     * Retrieve a ride conversation
     * @api {GET} /api/libs/user/chat/conversation
     * @api {GET} /api/libs/provider/chat/conversation
     * @param ConversationFormRequest $request
     * @return ConversationsResource
     */
    public function getConversation(ConversationFormRequest $request)
	{
		if($request->request_id) {
			$conversationArray = ConversationRequest::getConversations($request->request_id, $request->ledger_id);
		} else {
			$conversationArray = ConversationRequest::getInbox($request->ledger_id);
		}

		return new ConversationsResource([
			"conversationArray" => $conversationArray,
			"ledger_id" => $request->ledger_id,
			"sender_type" => $request->sender_type
		]);
    }
    
    /**
     * Retrieve messages of a ride conversation
     * @api {GET} /api/libs/user/chat/messages
     * @api {GET} /api/libs/provider/chat/messages
     * @param MessageListFormRequest $request
     * @return ChatMessagesResource
     */
    public function getMessages(MessageListFormRequest $request)
	{
		$messages = ConversationRequest::getMessagesByConversationId($request->conversation_id, $request->limit, $request->offset);
		
		$request_id = ConversationRequest::getByConversationId($request->conversation_id)->request_id;
		
		return new ChatMessagesResource([
			"messages" => $messages,
			"request_id" => $request_id,
			"user_id" => $request->ledger->id
		]);
    }
    
    /**
     * Set message of a ride conversation as seen
     * @api {GET} /api/libs/user/chat/seen
     * @api {GET} /api/libs/provider/chat/seen
     * @param MessageSeenFormRequest $request
     * @return json
     */
    public function setMessagesSeen(MessageSeenFormRequest $request)
	{
		$message = Message::find($request->message_id);
		ConversationRequest::setMessagesAsSeen($message, $request->u_id);

		event(new EventReadMessage($message->id));

		return response()->json([
            "success" => true
        ]);
	}

	/**
     * Retrieve a ride conversation
     * @api {GET} /api/libs/user/chat/conversation
     * @api {GET} /api/libs/provider/chat/conversation
     * @param ConversationFormRequest $request
     * @return ConversationsResource
     */
    public function responseQuickReply(Request $request, $id)
	{
		//dd($request);
		try {
			DeliveryPackage::where('id', $id)->update(['accepted_status' => $request->status]);
			if(Message::where('delivery_package_id', $id)
				->where('conversation_id', $request->conversation)
				->update(['response_quick_reply' => $request->status]))
			{
				$message = new Message();
				//dd($message);
				$message->create([
					'message' => $request->auto_response,
					'conversation_id' => $request->conversation,
					'user_id' => $request->receiver,
					'is_seen' => 0,
				]);
			}
			
			
			
			return response()->json([
				'success' => true,
			]);
		}
		catch (\Throwable $th) {
			return response()->json([
				'success' => false,
				'message' => $th->getMessage(),
			]);
			//dd($th->getMessage());
			Log::error($th->getMessage());
		}
		
    }
}