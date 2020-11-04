<?php

namespace Codificar\Chat\Http\Requests;

use App\Http\Requests\BaseRequest;
use Nahid\Talk\Conversations\Conversation;
use Provider, User, Ledger;

class MessageListFormRequest extends BaseRequest {
	
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
	public function rules() {
		return [
			"conversation_id" => "required|integer",
			"user_id" => 'integer'.($this->sender_type == 'admin'?'|required':''),
			"conversation" => "required",
		];
	}

	public function messages() {
		return [
			'conversation.required' => trans('requests.conversation_not_found')
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

		if($sender_type == "provider") {
			$provider = $this->provider;
			//$ledger = Ledger::findByProviderId($this->provider_id);
			$ledger = $provider->getLedger();
		} else {
			$user = User::find($this->user);
			//$ledger = Ledger::findByUserId($this->user_id);
			$ledger = $user->getLedger();
        }
        
        $conversation = Conversation::find($this->conversation_id);
        
		if($ledger and $conversation and ($conversation->user_one == $ledger->id or $conversation->user_two == $ledger->id)) {
			$this->merge([ "conversation" => $conversation ]);
        }
        
		$this->merge(
            [ 
                "sender_type" => $sender_type,
                "ledger" => $ledger
            ]
        );
	}
}
