<?php

namespace Codificar\Chat\Http\Requests;

use App\Http\Requests\BaseRequest;
use Ledger;

class SendMessageRequest extends BaseRequest
{
    /**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			"receiver_id"	=> "required|integer",
			"message"		=> "string".($this->type == "text"?"|required":""),
			"bid"			=> "numeric".($this->type == "bid"?"|required":""),
			"type"			=> "in:text,bid"
		];
    }
    
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation() {
		$sender_type = request()->segments()[2];
		
		$this->is_admin = 0;
		if($sender_type == "provider") {
			$user = $this->receiver_id;
			$provider = $this->provider->ledger->id;
			\Talk::setAuthUserId($provider);
			$sender_id = $provider;
		} else {
			if($sender_type == "user") {
				$user = $this->user->ledger->id;
			} else {//O admin loga em nome do usuário
				$this->is_admin = 1;
				$user = Ledger::findByUserId($this->user_id)->id;
			}
			\Talk::setAuthUserId($user);
			$sender_id = $user;
			$provider = $this->receiver_id;
		}
		$this->merge([
			"sender_type" => $sender_type,
			"sender_id" => $sender_id,
			"user_id" => $user,
			"provider_id" => $provider,
			"ledger_receiver" => Ledger::find($this->receiver_id),
			'is_admin' => $this->is_admin
		]);
	}
}