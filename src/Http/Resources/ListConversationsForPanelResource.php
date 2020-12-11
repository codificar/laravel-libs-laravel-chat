<?php

namespace Codificar\Chat\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ListConversationsForPanelResource extends JsonResource
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

        foreach ($conversations as $item) {
            $receiver = $item->user_one == $this['sender_id'] ?
                $item->usertwo->provider :
                $item->userone->provider;

            $message = $item->messages[count($item->messages) -1];
            $ride = $item['request_id'] == 0 ? '' : ' #' . $item['request_id'];
            
            $data = [
                'id' => $receiver->id,
                'conversation_id' => $item['id'],
                'request_id' => $item['request_id'],
                'first_name' => $receiver->first_name,
                'last_name' => $receiver->last_name,
                'full_name' => $receiver->first_name . ' ' . $receiver->last_name . $ride,
                'picture' => $receiver->picture,
                'last_message' => $message->message,
                'time' => $message->humans_time,
                'messages' => $item['messages']
            ];

            $response[] = $data;
        }
        
        return [
            'success' => true,
            'conversations' => $response
        ];
    }
}