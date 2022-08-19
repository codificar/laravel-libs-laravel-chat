<?php

namespace Codificar\Chat\Http\Requests;

use Codificar\Chat\Http\Utils\Helper;
use Illuminate\Foundation\Http\FormRequest;

class GetHelpChatMessageRequest extends FormRequest
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
            'request_id' => 'required'
        ];
    }

    protected function prepareForValidation() {
        try {
            $senderType = strtolower(
                get_class($this->userSystem)
            );
    
            $senderLedger = Helper::getLedger($senderType, $this->userSystem->id);
            
            $this->merge([
                "sender_type" => $senderType,
                "sender_id" => $senderLedger ? $senderLedger->id : null
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
            \Log::info('GetHelpChatMessageRequest > prepareForValidation > error: ' . $e->getMessage());
        }
	}
}
