<?php

namespace Codificar\Chat\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Codificar\Chat\Events\EventConversation;
use Codificar\Chat\Http\Requests\HelpChatMessageRequest;
use Codificar\Chat\Http\Resources\RequestHelpListResource;
use Codificar\Chat\Models\RequestHelp;
use Ledger, Admin, Auth, Redirect, Settings, Requests;
use App\Models\RequestPoint;
use Codificar\Chat\Http\Requests\GetHelpChatMessageRequest;
use Codificar\Chat\Http\Resources\ChatMessagesResource;
use Nahid\Talk\Conversations\Conversation;

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
    public function adminHelpChat($help_id)
    {
        $admin = Admin::find(Auth::guard('web')->user()->id);
		if (!$admin) {
			return Redirect::to("/admin/home");
		}
		if ($admin->profile_id == 6){
			abort(404);
        }
        
        $conversation = Conversation::whereHelpId($help_id)->first();

        if (!$conversation) {
            return Redirect::to("/admin/report_help");
        }

        $conversation->user_two = $admin->getLedger()->id;
        $conversation->save();

        $userHelped = Ledger::getById($conversation->user_one)->getUserTypeInstance();

        return view('chat::help_chat', [
            "environment" => "admin",
            'requestPoints' => RequestPoint::whereRequestId($conversation->request_id)->get(),
            'user' => [
                'id' => $userHelped->getLedger()->id,
                'user_id' => $userHelped->id,
                'token' => $userHelped->token,
                'name' => $userHelped->getFullName(),
                'image' => $userHelped->picture
            ],
            "maps_api_key" => Settings::getGoogleMapsApiKey(),
            'request' => Requests::find($conversation->request_id),
            'messages' => $conversation->messages->toArray(),
            'admin' => [
                'id' => $admin->getLedger()->id,
                'user_id' => $admin->id,
                'token' => $admin->remember_token,
                'name' => $admin->profile->name,
                'image' => \Theme::getLogoUrl()
            ],
            'convId' => $conversation->id
        ]);
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

        event(new EventConversation($message));

        $receiver = Ledger::find($conversation->user_one);

        $payload = [
            'request_id' => $conversation->request_id,
            'is_help' => true
        ];

        if (
            $request->sender_type == 'admin' && 
            $receiver && 
            $receiver->getTypeAttribute() == 'user'
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
            $receiver->getTypeAttribute() == 'provider'
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
