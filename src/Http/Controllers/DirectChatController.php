<?php

namespace Codificar\Chat\Http\Controllers;

use Grimzy\LaravelMysqlSpatial\Types\Point;
use App\Http\Controllers\Controller;
use Codificar\Chat\Events\EventConversation;
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

class DirectChatController extends Controller
{
    /**
     * @api {POST} /api/libs/set_direct_message
     * Send direct message
     * @param SendDirectRequest $request
     * @return json
     */
    public function sendDirectMessage(SendDirectRequest $request)
    {
        $conversation = $this->geOrCreatetConversation($request);

        \Talk::setAuthUserId($request->sender_id);
        $message = \Talk::sendMessage($conversation->id, $request->message);

        event(new EventConversation($message));

        return response()->json([
            "success" => true, 
            "conversation_id" => $message->conversation_id
        ]);
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

        if ($request->sender_type == 'user') {
            $conversations = Conversation::whereUserOne($request->sender_id)->whereRequestId(0)->pluck('user_two');
            $receivers = Ledger::whereIn('id', $conversations)->with('provider')->get();
        } else {
            $conversations = Conversation::whereUserTwo($request->sender_id)->whereRequestId(0)->pluck('user_one');
            $receivers = Ledger::whereIn('id', $conversations)->with('user')->get();
        }

        return new ListDirectConversationResource([
            'receivers' => $receivers,
            'receiver_type' => $request->sender_type == 'user' ? 'user' : 'provider' 
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
        $conversation = null;

        if ($request->sender_type == 'user') {
            $conversation = Conversation::whereUserOne($request->sender_id)
                ->whereUserTwo($request->receiver_id)
                ->whereRequestId(0)
                ->first();
            
            return $conversation;
        }

        return Conversation::whereUserOne($request->receiver_id)
            ->whereUserTwo($request->sender_id)
            ->whereRequestId(0)
            ->first();
        
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

        $providersQuery = $this->getProvidersWithinDistance($referencePoint, $distanceSearchRadius * $unitMultiply);

        if ($request->name) {
            $providersQuery->where(
                DB::raw('CONCAT_WS(" ", first_name, last_name)'), 
                'like', 
                '%' . $request->name . '%'
            );
        }

        $providers = $providersQuery->limit(10)->get();

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

        return $query;
    }

}