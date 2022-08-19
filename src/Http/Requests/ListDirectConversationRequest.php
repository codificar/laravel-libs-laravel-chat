<?php

namespace Codificar\Chat\Http\Requests;

use Codificar\Chat\Http\Utils\Helper;
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
        try {
            $senderType = $this->userType;
            $requestId = $this->request_id ? $this->request_id : null;
            $senderLedger = Helper::getLedger($senderType, $this->userSystem->id);
            $page = isset($this->page) ? $this->page : 1;
            $name = isset($this->name) ? $this->name : "";
    
            $this->merge([
                "sender_type" => $senderType,
                "sender_id" => $senderLedger ? $senderLedger->id : null,
                "request_id" => $requestId,
                "page" => $page,
                "name" => $name
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
            \Log::info('ListDirectConversationRequest > prepareForValidation > error: ' . $e->getMessage());
        }
	}
}