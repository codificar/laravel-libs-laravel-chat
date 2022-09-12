<?php

namespace Codificar\Chat\Http\Requests;

use App\Models\Institution;
use Illuminate\Foundation\Http\FormRequest;
use Nahid\Talk\Conversations\Conversation;
use Provider, User, Ledger;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Requests;

class MessageListFormRequest extends FormRequest {
	
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

		$conversation = Conversation::find($this->conversation_id);

		if($sender_type == "provider") {
			$provider = $this->provider ? 
				$this->provider :
				Provider::find($this->provider_id);
			$ledger = Ledger::where('provider_id', $provider->id)->first();
		
		} else if($sender_type == "corp") {
			$request = Requests::find($this->request_id);
			
			if($request) {
				$this->user = Institution::find($request->institution_id);
				$provider = $this->provider ? 
					$this->provider :
					Provider::find($this->provider_id);
				$ledger = Ledger::where('provider_id', $provider->id)->first();
				
				$conversation = Conversation::where('request_id', $this->request_id)->first();
				$this->conversation_id = isset($conversation->id) && !empty($conversation->id) 
					? $conversation->id
					: null;
			}

		} else {
			$user = $this->user ? 
				$this->user : 
				User::find($this->user_id);

			$ledger = Ledger::where('user_id', $user->id)->first();
        }
        
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

	/**
     * Returns a json if validation fails
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
	 * 
     * @return Json {'success','errors','error_code'}
     *
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success'       => false,
                'errors'        => $validator->errors()->all(),
                'error_code'    => \ApiErrors::REQUEST_FAILED
            ])
        );
    }
}
