<?php

namespace Codificar\Chat\Http\Controllers;

use App\Http\Controllers\Controller;
use Codificar\Chat\Models\ConversationRequest;
use Codificar\Chat\Http\Requests\SendMessageRequest;
use Codificar\Chat\Http\Requests\ConversationFormRequest;
use Codificar\Chat\Http\Requests\MessageListFormRequest;
use Codificar\Chat\Http\Requests\MessageSeenFormRequest;
use Codificar\Chat\Http\Resources\ConversationsResource;
use Codificar\Chat\Http\Resources\ChatMessagesResource;
use Codificar\Chat\Events\EventConversation;
use Codificar\Chat\Events\EventNewConversation;
use Codificar\Chat\Events\EventReadMessage;
use Requests, Admin, Auth;
use Log;
use Nahid\Talk\Messages\Message;

class RideChatController extends Controller
{
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
				event(new EventNewConversation($ride, $message->conversation_id, $request->receiver_id));
			}

			if ($request->is_admin) {
				$admin = Admin::find(Auth::guard('web')->user()->id);
				if ($admin) {
					$message->admin_id = $admin->id;
					$message->save();
				}
			}
			
            event(new EventConversation($message));
            
			Log::notice("sender_type:". $request->sender_type);
			Log::notice("receiver_id:". $request->receiver_id);
			if ($request->sender_type == 'provider') {
				Log::notice("notifica user_id:" . $request->ledger_receiver->user_id);
				// notifica user
				$this->sendNotificationMessageReceived(trans('requests.new_message'), $message->conversation_id, $message->message, $request->ledger_receiver->user_id, 'user');
			}
			else {
				Log::notice("notifica provider_id:". $request->ledger_receiver->provider_id);
				// notifica provider
				$this->sendNotificationMessageReceived(trans('requests.new_message'), $message->conversation_id, $message->message, $request->ledger_receiver->provider_id, 'provider');
			}

		} catch(\Exception $ex) {
			Log::error($ex->getMessage().$ex->getTraceAsString());
		}

		return response()->json([
            "success" => true, 
            "conversation_id" => $message->conversation_id
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

		event(new EventReadMessage($message));

		return response()->json([
            "success" => true
        ]);
	}
}