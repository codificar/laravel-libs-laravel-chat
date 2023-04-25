<?php

namespace Codificar\Chat\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessagesResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array
	 */
	public function toArray($request)
	{
		$messages = $this["messages"] ? $this["messages"]->toArray() : $this["messages"];
		
		return [
			'success' => true,
			'messages' => $messages,
			'request_id' => $this["request_id"],
			'user_ledger_id' => $this["user_id"],
			'conversation_id' => isset($this["conversation_id"]) && !empty($this["conversation_id"]) 
				? $this["conversation_id"] 
				: 0
		];
	}
}
