<?php

namespace Codificar\Chat\Http\Requests;

use Codificar\Chat\Http\Utils\Helper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Provider;
use User;

class SendDirectRequest extends FormRequest
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

        $senderLedger = Helper::getLedger($senderType, $this->userSystem->id);
        $receiver = null;
        $receiverLedger = null;
        $receiverType = 'user';
        
        if ($senderType == 'user') {
            $receiver = Provider::find($this->receiver);
            $receiverType = 'provider';
        } else {
            $receiver = User::find($this->receiver);
        }
        
        if ($receiver)
            $receiverLedger = Helper::getLedger($receiverType, $receiver->id);
        
		$this->merge([
			"sender_type" => $senderType,
            "sender_id" => $senderLedger ? $senderLedger->id : null,
            "ledger_receiver" => $receiverLedger,
            "receiver_id" => $receiverLedger ? $receiverLedger->id : null
		]);
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