<?php

namespace Codificar\Chat\Http\Requests;

use App\Http\Requests\BaseRequest;
use Codificar\Chat\Models\ConversationRequest;
use Ledger;
use Nahid\Talk\Messages\Message;

class MessageSeenFormRequest extends BaseRequest
{
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
		$message = Message::find($this->message_id);
		if(isset($this->provider)) {
			$id = $this->provider->ledger->id;
		} else {
			$id = Ledger::findByUserId($this->user_id)->id;
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
}
