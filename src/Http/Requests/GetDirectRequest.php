<?php

namespace Codificar\Chat\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Provider;
use User;

class GetDirectRequest extends FormRequest
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
            "sender_id" => "required",
            "receiver_id" => "required"
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
        $receiver = null;
        $receiverLedger = null;

        if ($senderType == 'user') {
            $receiver = Provider::find($this->receiver);
        } else {
            $receiver = User::find($this->receiver);
        }

        if ($receiver)
            $receiverLedger = $receiver->getLedger();
        
		$this->merge([
			"sender_type" => $senderType,
            "sender_id" => $senderLedger ? $senderLedger->id : null,
            "receiver_id" => $receiverLedger ? $receiverLedger->id : null
		]);
	}
}