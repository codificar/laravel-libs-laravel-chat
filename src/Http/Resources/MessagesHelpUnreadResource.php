<?php

namespace Codificar\Chat\Http\Resources;

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
     * @return RequestHelp 
     */
    private function handleHelpMessage($helpMessages)
    {
        foreach($helpMessages as &$helpMessage) {
            $helpMessage->link = \URL::Route('libHelpReportId', ['helpId' => $helpMessage->id]);
            $helpMessage->username = $helpMessage->provider_fullname;
            if($helpMessage->author && $helpMessage->author == 'user') {
                $helpMessage->username = $helpMessage->user_fullname;
            } 
        };
        return $helpMessages;
    }
}
