<?php

namespace Codificar\Chat\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Nahid\Talk\Conversations\Conversation;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class MessageListFormRequest extends FormRequest 
{	
	private const SEGMENT_API 			= 1;
	private const SEGMENT_LIBS 			= 2;
	private const SEGMENT_ADMIN 		= 3;
	private const SEGMENT_CHAT 			= 4;
	private const SEGMENT_CONVERSATION 	= 5;
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
		$senderType = request()->segment(self::SEGMENT_ADMIN);
		if($this->userType) {
			$senderType = $this->userType;
		}

		$ledger = $this->getLedger($senderType);
		$conversation = Conversation::find($this->conversation_id);

		if($ledger and $conversation and ($conversation->user_one == $ledger->id or $conversation->user_two == $ledger->id)) {
			$this->merge([ "conversation" => $conversation ]);
        }
        
		$this->merge(
            [ 
                "sender_type" => $senderType,
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


	private function getLedger($senderType)
	{
		$ledger = null;
		
		switch ($senderType) {
			case 'provider':
				$provider = $this->provider ? 
					$this->provider :
					\Provider::find($this->provider_id);
				$ledger = $provider->ledger;
				break;
			case 'corp':
				$ride = \Requests::find($this->request_id);			
				if($ride) {
					$this->user = $ride->institution;
					$ledger = $ride->confirmedProvider->ledger;
				}
				break;
			default:
				$user = $this->user ? 
					$this->user : 
					\User::find($this->user_id);
				if($user){
					$ledger = $user->ledger;
				}
				break;
		}
		return $ledger;
	}
}
