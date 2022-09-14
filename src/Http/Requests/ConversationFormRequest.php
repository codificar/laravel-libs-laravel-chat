<?php

namespace Codificar\Chat\Http\Requests;

use App\Models\Institution;
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

		// verifica se tem uma conversation para a request 
		$convId = null;
		if($this->request_id) {
			$convRequest = \ConversationRequest::where(['request_id' => $this->request_id])->first();
			if(isset($convRequest->conversation_id) && !empty($convRequest->conversation_id)) {
				$convId = $convRequest->conversation_id;
			}
		}
		
		//verifica se tem uma conversation criada, caso não, 
		// cria uma para se inscrever no socket da conversação de forma correta
		$conversation = \Conversation::find($convId);
		if ($this->request_id && !$conversation) {
			try {
				$request = \Requests::find($this->request_id);
				$userOne = \Ledger::where(['user_id' => $request->user_id])->first()->id;
				$userTwo = \Ledger::where(['provider_id' => $request->current_provider])->first()->id;

				$conversation = new \Conversation();
				$conversation->user_one = $userOne;
				$conversation->user_two = $userTwo;
				$conversation->request_id = $request->id;
				$conversation->help_id = null;
				$conversation->status = 1;
				$conversation->save();

				$convRequest = new \ConversationRequest();
				$convRequest->conversation_id = $conversation->id;
				$convRequest->request_id = $request->id;
				$convRequest->provider_accepted = isset($request->current_provider) && !empty($request->current_provider) 
					? 1
					: 0;
				$convRequest->user_accepted = 0;
				$convRequest->save();
			} catch (\Exception $e) {
				\Log::error($e->getMessage());
			}
		}

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
