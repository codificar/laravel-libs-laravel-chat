<?php

namespace Codificar\Chat\Http\Resources;

use Codificar\Chat\Http\Utils\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

class FilterConversationsResource extends JsonResource
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
            $receiver = $item['user_one'] == $this['sender_id'] ?
                Helper::getUserTypeInstance($item['user_two']) :
                Helper::getUserTypeInstance($item['user_one']);

            if ($receiver && count($item['messages']) > 0) {
                $message = $item['messages'][count($item['messages']) -1];
                $ride = $item['request_id'] == 0 ? '' : ' #' . $item['request_id'];
                
                $data = [
                    'id' => $receiver->ledger_id,
                    'conversation_id' => $item['id'],
                    'request_id' => $item['request_id'],
                    'first_name' => $receiver->first_name,
                    'last_name' => $receiver->last_name,
                    'full_name' => $receiver->full_name . $ride,
                    'picture' => $receiver->picture,
                    'last_message' => $message['message'],
                    'time' => $message['humans_time'],
                    'messages' => $item['messages']
                ];
    
                $response[] = $data;
            }

        }
        
        return [
            'success' => true,
            'conversations' => $response,
            'last_page' => $this['last_page'],
            'current_page' => $this['current_page'],
            'locations' => $this['locations']
        ];
    }
}