<?php

namespace Codificar\Chat\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessagesPanicTodayResource extends JsonResource
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
            'panic_messages' => $this->handlePanicMessage($this['messages']),
            'total_unread' =>  $totalUnread
        ];
    }

    /**
     * Handle help message model
     * @param Array $panicMessages
     * @return Array 
     */
    private function handlePanicMessage($panicMessages)
    {
        foreach($panicMessages as $key => &$panicMessage) {
            $panicMessages[$key]['link'] = \URL::Route('libPanicSee', ['panicId' => $panicMessage->id]); 
        };
        return $panicMessages;
    }
}
