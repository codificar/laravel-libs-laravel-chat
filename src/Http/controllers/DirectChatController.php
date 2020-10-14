<?php

namespace Codificar\Chat\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatMessagesResource;
use Codificar\Chat\Events\EventConversation;
use Codificar\Chat\Http\Requests\GetDirectRequest;
use Codificar\Chat\Http\Requests\ListDirectConversationRequest;
use Codificar\Chat\Http\Requests\SendDirectRequest;
use Codificar\Chat\Http\Resources\ListDirectConversationCollection;
use Codificar\Chat\Http\Resources\ListDirectConversationResource;
use Ledger;
use Nahid\Talk\Conversations\Conversation;
use Provider;

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
            $receivers = Ledger::whereIn('id', $conversations)->with('user')->get();
            $conversations = Conversation::whereUserTwo($request->sender_id)->whereRequestId(0)->pluck('user_one');
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
}