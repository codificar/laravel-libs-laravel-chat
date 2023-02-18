<?php

namespace Codificar\Chat\Http\Requests;

use Codificar\Chat\Http\Utils\Helper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Provider;
use User;

class SendBulkMessageRequest extends FormRequest
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
            "message" => "string|required",
            "sender_id" => "required",
            "picture" => "file|mimes:jpeg,png,jpg|max:2048"
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
    
            $senderLedger = Helper::getLedger($senderType, $this->userSystem->id);
            
            $this->merge([
                "sender_type" => $senderType,
                "senderLedger" => $senderLedger,
                "sender_id" => $senderLedger ? $senderLedger->id : null
            ]);
        } catch (\Exception $e) {
            \Log::info('SendBulkMessageRequest > prepareForValidation > error: ' . $e->getMessage() . $e->getTraceAsString());
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