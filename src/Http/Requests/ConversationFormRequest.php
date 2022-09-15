<?php

namespace Codificar\Chat\Http\Requests;

use App\Models\Institution;
use Codificar\Chat\Models\ConversationRequest;
use Illuminate\Foundation\Http\FormRequest;
use Nahid\Talk\Conversations\Conversation;
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
			"request_id" => "integer".(($this->sender_type == "admin" || $this->sender_type == "corp") ?"|required":"")
		];
	}

	/**
     * Prepare the data for validation.
     *
     * @return void
     */
	protected function prepareForValidation() {

		$sender_type = request()->segments()[2];
		
		if($this->userType) {
			$sender_type = $this->userType;
		}

		$conversation = ConversationRequest::getOrCreateConversationChat($this->request_id);
		$this->conversation_id = isset($conversation->id) 
			? $conversation->id
			: 0;

		if($sender_type == "provider") {
			$ledger_id = $this->provider->ledger->id;
		} else if($sender_type == "admin") {

			$request = \Requests::find($this->request_id);			
			if($request) {
				$this->user = \User::find($request->user_id);
				$ledger_id = $this->user->ledger->id;
			}
		}  else if($sender_type == "corp") {
			$request = \Requests::find($this->request_id);
			if($request) {
				$this->user = \User::find($request->user_id);
			}
			
			if($this->user){
				$ledger_id = $this->user->getLedger()->id;
			}
			
		} else {
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
