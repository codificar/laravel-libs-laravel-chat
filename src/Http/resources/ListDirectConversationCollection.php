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
        return [
            'id' => $this['provider']['id'],
            'first_name' => $this['provider']['first_name'],
            'last_name' => $this['provider']['last_name'],
            'picture' => $this['provider']['picture']
        ];
    }
}