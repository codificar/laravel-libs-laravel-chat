<?php

namespace Codificar\Chat\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListDirectConversationRequest  extends FormRequest
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
            'sender_type' => 'required',
            'sender_id' => 'required'
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation() {
        $senderType = strtolower(
            get_class($this->userSystem)
        );

        $senderLedger = $this->userSystem->getLedger();
        
		$this->merge([
            "sender_type" => $senderType,
            "sender_id" => $senderLedger ? $senderLedger->id : null
		]);
	}
}