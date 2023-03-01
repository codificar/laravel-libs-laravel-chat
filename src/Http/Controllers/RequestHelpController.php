<?php

namespace Codificar\Chat\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Codificar\Chat\Events\EventConversation;
use Codificar\Chat\Events\EventNewHelpMessageNotification;
use Codificar\Chat\Events\EventReadMessage;
use Codificar\Chat\Http\Requests\HelpChatMessageRequest;
use Codificar\Chat\Http\Resources\RequestHelpListResource;
use Codificar\Chat\Models\RequestHelp;
use Ledger, Redirect, Settings, Requests;
use Codificar\Chat\Http\Requests\GetHelpChatMessageRequest;
use Codificar\Chat\Http\Resources\ChatMessagesResource;
use Codificar\Chat\Http\Utils\Helper;
use Codificar\Chat\Models\Conversations;
use Codificar\Chat\Repositories\MessageRepository;

class RequestHelpController extends Controller
{
    /**
     * Render report of help notes
     * @return view
     */
    public function renderReportPage()
    {
        return view('chat::help_report');
    }

    /**
     * Render admin help chat blade
     */
    public function adminHelpChat(Request $request, $helpId, MessageRepository $messageRepository)
    {
        try {
            $admin = $request->user;
            $conversation = Conversations::whereHelpId($helpId)->first();
    
            if (!$conversation) {
                return Redirect::to("/admin/report_help");
            }
            
            $adminLedger = Helper::getLedger('admin', $admin->id);
            $conversation->user_two = $adminLedger->id;
            $conversation->save();
            
            $userHelped = Helper::getUserTypeInstance($conversation->user_one);
            
            if($conversation->lastMessageUnread) {
                $conversationId = $conversation->id;
                $messageId = $conversation->lastMessageUnread->id;
                $messageRepository->setMessagesAsSeen($conversationId, $messageId);
                event(new EventReadMessage($messageId));
            }

            return view('chat::help_chat', [
                "environment" => "admin",
                'requestPoints' => [],
                'user' => [
                    'id' => $userHelped->ledger_id,
                    'user_id' => $userHelped->id,
                    'token' => $userHelped->token,
                    'name' => $userHelped->full_name,
                    'image' => $userHelped->picture
                ],
                'request' => Requests::find($conversation->request_id),
                'messages' => $conversation->messages->toArray(),
                'admin' => [
                    'id' => $adminLedger->id,
                    'user_id' => $admin->id,
                    'token' => $admin->remember_token,
                    'name' => $admin->profile->name,
                    'image' => \Theme::getLogoUrl()
                ],
                'conversation_id' => $conversation->id
            ]);
        } catch (\Exception $e) {
            \Log::info('RequestHelpController > adminHelpChat > error: ' . $e->getMessage() . $e->getTraceAsString() );
            return new \Exception($e->getMessage());
        }
    }

    /**
     * Get the report of request help notes
     * @api {GET} /api/libs/help_list
     * @param Request $request
     * @return RequestHelpListResource
     */
    public function fetch(Request $request)
    {
        return new RequestHelpListResource([
            'request_help' => RequestHelp::fetch(
                $request->page,
                json_decode($request->filter)
            )
        ]);
    }

    /**
     * Get help chat messages
     * @param GetHelpChatMessageRequest $request
     * @return ChatMessagesResource
     */
    public function getHelpChatMessage(GetHelpChatMessageRequest $request)
    {
        $messages = RequestHelp::getMessages($request);

        return new ChatMessagesResource([
			"messages" => $messages,
			"request_id" => $request->request_id,
			"user_id" => $request->sender_id
		]);
    }

    /**
     * Send new message in help chat
     * @param HelpChatMessageRequest $request
     * @return json
     */
    public function setHelpChatMessage(HelpChatMessageRequest $request)
    {
        $conversation = RequestHelp::getConversation($request);
        
        \Talk::setAuthUserId($request->sender_id);
        $message = \Talk::sendMessage($conversation->id, $request->message);

        event(new EventConversation($message->id));
        if($conversation->help_id) {
            event(new EventNewHelpMessageNotification($conversation->help_id));
        }

        $receiver = Ledger::find($conversation->user_one);

        $payload = [
            'request_id' => $conversation->request_id,
            'is_help' => true
        ];

        if (
            $request->sender_type == 'admin' && 
            $receiver && 
            $receiver->user_id
        ) {
            $this->sendNotificationMessageReceived(
                trans('laravelchat::laravelchat.new_message'), 
                $message->conversation_id, 
                $message->message, 
                $receiver->user_id, 
                'user',
                $payload
            );
        } else if (
            $request->sender_type == 'admin' && 
            $receiver && 
            $receiver->provider_id
        ) {
            $this->sendNotificationMessageReceived(
                trans('laravelchat::laravelchat.new_message'), 
                $message->conversation_id, 
                $message->message, 
                $receiver->provider_id, 
                'provider',
                $payload
            );
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
	public function sendNotificationMessageReceived($title, $conversation_id, $contents, $model_id, $type, $payload = null) {
		try {
			// Send Notification
			$message = array(
				'success' => true,
				'conversation_id' => $conversation_id,
				'message' => $contents
			);
			//envia notificação push
			send_notifications($model_id, $type, $title, $message, null, $payload);
		} catch (\Exception $ex) {
			return $ex->getMessage().$ex->getTraceAsString();
		}
	}
}
