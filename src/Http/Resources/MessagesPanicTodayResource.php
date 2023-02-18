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
            'panic_messages' => $this['messages'],
            'total_unread' =>  $totalUnread
        ];
    }
}
