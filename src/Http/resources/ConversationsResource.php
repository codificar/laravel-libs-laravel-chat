<?php

namespace Codificar\Chat\Http\Resources;

use Codificar\Chat\Models\ConversationRequest;
use Illuminate\Http\Resources\Json\JsonResource;
use Ledger, Requests, Theme;

class ConversationsResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array
	 */
	public function toArray($request)
	{
		$cArray = $this['conversationArray'];
		$conversations = [];

		if ($cArray->isEmpty() and isset($request->request_id)) {
			$ride = Requests::find($request->request_id);
			$ledger = $this["sender_type"] == 'provider' ? Ledger::findByUserId($ride->user_id) : Ledger::findByProviderId($ride->confirmed_provider);

			if ($ledger) {
				$user = $this["sender_type"] == 'provider' ? $ledger->user : $ledger->provider;
				return [
					"success" => true,
					'ledger_id' => $this["ledger_id"]  ,
					"conversations" => [[
						"id" => 0, //Ainda não tem id
						"request" => [
							"id" => $ride->id,
							//"product" => $ride->loadProduct->name
						],
						"user" => [
							"id" => $ledger->id,
							"name" => $ledger->fullname,
							"image" => $user->thumb ? $user->thumb : Theme::getLogoUrl()
						]
					]]
				];
			}
			else 
				return [
					"success" => false,
					'ledger_id' => $this["ledger_id"] 
				];
		}
		foreach ($cArray as $conversation) {
			$user = $conversation->conversation->userone->id == $this["ledger_id"]
				? $conversation->conversation->usertwo
				: $conversation->conversation->userone;
			$type = $user->type;
			$messages = $conversation->conversation->messages;
			$conversations[] = [
				"id" => $conversation->conversation_id,
				"last_bid" => $conversation->last_bid,
				"request" => [
					"id" => $conversation->request->id,
					//"product" => $conversation->request->loadProduct->name
				],
				"user" => [
					"id" => $user->id,
					"name" => $user->full_name,
					"image" => ($user->$type and $user->$type->thumb) ? $user->$type->thumb : Theme::getFaviconUrl()
				],
				"last_message" => !$messages->isEmpty() ? [
					"id" => $messages[0]->id,
					"message" => $messages[0]->message,
					"type" => preg_match('/' . trans('requests.new_bid') . '.*/', $messages[0]->message) ? 'bid' : 'text',
					"date" => $messages[0]->humans_time,
					"created_at" => $messages[0]->created_at->format('h:i d/m/Y')
				] : [],
				"new_messages" => ConversationRequest::countMessagesUnseen($messages, $this['ledger_id'])
			];
		}
		return [
			'success' => true,
			'ledger_id' => $this["ledger_id"] ,
			'conversations' => $conversations 
		];
	}
}
