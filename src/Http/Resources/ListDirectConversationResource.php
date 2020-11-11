<?php

namespace Codificar\Chat\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ListDirectConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $response = [];

        $conversations = $this['conversations'];
        $receiverType = $this['sender_type'] == 'user' ? 'usertwo' : 'userone';

        foreach ($conversations as $item) {
            $receiver = $receiverType == 'usertwo' ? 
                $item->usertwo->provider :
                $item->userone->user;

            $message = $item->messages[count($item->messages) -1];
            
            $data = [
                'id' => $receiver->id,
                'first_name' => $receiver->first_name,
                'last_name' => $receiver->last_name,
                'picture' => $receiver->picture,
                'last_message' => $message->message,
                'time' => $message->humans_time
            ];

            $response[] = $data;
        }
        
        return [
            'success' => true,
            'conversations' => $response
        ];
    }
}