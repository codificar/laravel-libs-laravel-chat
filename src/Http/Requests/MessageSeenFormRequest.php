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
			'message_id' => 'required|integer',
			'message' => 'required'
		];
	}

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
	protected function prepareForValidation()
	{
		$sender_type = request()->segments()[2];

		if($this->userType) {
			$sender_type = $this->userType;
		}
		$message = Message::find($this->message_id);
		$ledger = null;

		// TODO: verificar como o coorp vai se comportar ao vizualizar uma mensagem
		/*if($sender_type =='corp') {
			$cRequest = ConversationRequest::getByConversationId($message->conversation_id);
			
			$this->merge([
				'message' => $message,
				'u_id' => null,
				'request_id' => $cRequest->request_id
			]);
			return;
		}*/

		if(isset($this->provider)) {
			$ledger = Ledger::where('provider_id', $this->provider->id)->first();
			$id = $ledger->id;
		} else if(isset($this->user_id)) {
			$ledger = Ledger::where('user_id', $this->user_id)->first();
			$id = $ledger->id;
		} else {
			$ledger = Ledger::where('user_id', $this->user_id)->first();
			$id = $ledger->id;
		}

		if($message and ($message->conversation->user_one == $id or $message->conversation->user_two == $id)) {
			$cRequest = ConversationRequest::getByConversationId($message->conversation_id);
			$this->merge([
				'message' => $message,
				'u_id' => $id,
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
