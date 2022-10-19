<?php

namespace Codificar\Chat\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Codificar\Chat\Http\Utils\Helper;
use Provider;
use User;
use Ledger;

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
        try {
            $senderType = strtolower(
                get_class($this->userSystem)
            );
    
            $senderLedger = Helper::getLedger($senderType, $this->userSystem->id);
            $receiver = null;
            $receiverLedger = null;
            $receiverType = 'user';
    
            $receiverLedger = Ledger::find($this->receiver);
            
            $this->merge([
                "sender_type" => $senderType,
                "sender_id" => $senderLedger ? $senderLedger->id : null,
                "receiver_id" => $receiverLedger ? $receiverLedger->id : null
            ]);
        } catch (\Exception $e) {
            \Log::info('GetDirectRequest > prepareForValidation > error: ' . $e->getMessage() . $e->getTraceAsString());
        }
	}
}