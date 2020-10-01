<?php

namespace Codificar\Chat\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Requests, User;

class ConversationFormRequest extends FormRequest
{
	/**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
	}
	
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			"request_id" => "integer".($this->sender_type == "admin"?"|required":"")
		];
	}

	/**
     * Prepare the data for validation.
     *
     * @return void
     */
	protected function prepareForValidation() {
		$sender_type = request()->segments()[2];
		// dd($sender_type);
		if($sender_type == "provider") {
			$ledger_id = $this->provider->ledger->id;
		} else {
			if($sender_type == "admin") {
				$request = Requests::find($this->request_id);
				
				if($request) {
					$this->user = User::find($request->user_id);
				}
			}
			if($this->user){
				$ledger_id = $this->user->ledger->id;
			}
		}
		$this->merge([
			"sender_type" => $sender_type,
			'ledger_id' => $ledger_id,
			'user' => $this->user
		]);
	}
}
