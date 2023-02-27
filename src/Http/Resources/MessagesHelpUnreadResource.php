<?php

namespace Codificar\Chat\Http\Resources;

use DateTime;
use Illuminate\Http\Resources\Json\JsonResource;

class MessagesHelpUnreadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $totalUnread = $this['total_unread'] > 99
            ? '99+' 
            : $this['total_unread'];

        return [
            'success' => true,
            'help_messages' => $this->handleHelpMessage($this['messages']),
            'total_unread' =>  $totalUnread
        ];
    }

       /**
     * Handle help message model
     * @param RequestHelp $helpMessages
     * @return Array 
     */
    private function handleHelpMessage($helpMessages)
    {
        $outArray = [];
        foreach($helpMessages as $key => &$helpMessage) {
            $message = $helpMessage->conversations->lastMessageUnread;
            $outArray[$key]['id'] = $helpMessage->id;
            $outArray[$key]['message_id'] = $message->id;
            $outArray[$key]['conversation_id'] = $helpMessage->conversation_id;
            $outArray[$key]['author'] = $helpMessage->author;
            $outArray[$key]['provider_fullname'] = $helpMessage->provider_fullname;
            $outArray[$key]['user_fullname'] = $helpMessage->user_fullname;
            $outArray[$key]['link'] = \URL::Route('libHelpReportId', ['helpId' => $helpMessage->id]);
            $outArray[$key]['message'] = $message->message;
            $datetime = new DateTime($message->created_at);
            $outArray[$key]['datetime'] = $datetime->format('d/m/Y H:i:s'); 
            $outArray[$key]['username'] = $helpMessage->provider_fullname;
            if($helpMessage->author && $helpMessage->author == 'user') {
                $outArray[$key]['username'] = $helpMessage->user_fullname;
            } 
        };
        return $outArray;
    }
}
