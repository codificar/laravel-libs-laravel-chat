<?php

namespace Codificar\Chat\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RequestHelpListResource extends JsonResource
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
            'success' => true,
            'request_help' => $this['request_help']
        ];
    }
}
