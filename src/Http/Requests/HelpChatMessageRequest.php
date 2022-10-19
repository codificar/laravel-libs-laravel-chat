<?php

namespace Codificar\Chat\Http\Requests;

use Codificar\Chat\Http\Utils\Helper;
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
        try {
            $senderType = $this->userType;
    
            $senderLedger = Helper::getLedger($senderType, $this->userSystem->id);
            
            $this->merge([
                "sender_type" => $senderType,
                "sender_id" => $senderLedger ? $senderLedger->id : null,
                'ride' => Requests::find($this->request_id)
            ]);
        } catch (\Exception $e) {
            \Log::info('HelpChatMessageRequest > prepareForValidation > error: ' . $e->getMessage() . $e->getTraceAsString());
        }
	}
}
