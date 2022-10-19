<?php

namespace Codificar\Chat\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Codificar\Chat\Models\ConversationRequest;
use Ledger;
use Nahid\Talk\Messages\Message;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class MessageSeenFormRequest extends FormRequest
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
			'message_id' => 'required|integer'
		];
	}

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
	protected function prepareForValidation()
	{
		$message = Message::find($this->message_id);
		$ledgerId = null;

		if(isset($this->provider)) {
			$ledger = Ledger::where('provider_id', $this->provider->id)->first();
			$ledgerId = $ledger->id;
		} else {
			$ledger = Ledger::where('user_id', $this->user_id)->first();
			$ledgerId = $ledger->id;
		}

		if($message and $ledgerId and ($message->conversation->user_one == $ledgerId or $message->conversation->user_two == $ledgerId)) {
			$cRequest = ConversationRequest::getByConversationId($message->conversation_id);
			$this->merge([
				'message' => $message,
				'user_id' => $ledgerId,
				'request_id' => $cRequest->request_id
			]);
		}
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
