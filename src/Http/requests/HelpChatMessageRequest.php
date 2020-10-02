<?php

namespace Codificar\Chat\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Requests;

class HelpChatMessageRequest extends FormRequest
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
            "message"		=> "string".($this->type == "text"?"|required":""),
			"bid"			=> "numeric".($this->type == "bid"?"|required":""),
            "type"			=> "in:text,bid",
            'request_id'    => 'required'
        ];
    }

    protected function prepareForValidation() {
        $senderType = strtolower(
            get_class($this->userSystem)
        );

        $senderLedger = $this->userSystem->getLedger();
        
		$this->merge([
			"sender_type" => $senderType,
            "sender_id" => $senderLedger->id,
            'ride' => Requests::find($this->request_id)
		]);
	}
}
