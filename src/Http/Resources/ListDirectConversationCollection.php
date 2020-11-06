<?php

namespace Codificar\Chat\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ListDirectConversationCollection extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $type = $this['user_id'] ? 'user' : 'provider';

        return [
            'id' => $this[$type]['id'],
            'first_name' => $this[$type]['first_name'],
            'last_name' => $this[$type]['last_name'],
            'picture' => $this[$type]['picture']
        ];
    }
}