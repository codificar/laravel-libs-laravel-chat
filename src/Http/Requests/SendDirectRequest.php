<?php

namespace Codificar\Chat\Http\Requests;

use Codificar\Chat\Http\Utils\Helper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Provider;
use User;
use Ledger;

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
            "receiver_id" => "required",
            "picture" => "image"
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation() {
        $senderType = $this->userType;

        $senderLedger = Helper::getLedger($senderType, $this->userSystem->id);
        $receiver = null;
        $receiverLedger = null;
        $receiverName = '';
        $receiverPicture = '';

        $receiverLedger = Ledger::find($this->receiver);
        $receiver = $receiverLedger ? Helper::getUserTypeInstance($receiverLedger->id) : null;

        if ($receiver) {
            $receiverName = $receiver->first_name . ' ' . $receiver->last_name;
            $receiverPicture = $receiver->picture;
        }

        
		$this->merge([
			"sender_type" => $senderType,
            "sender_id" => $senderLedger ? $senderLedger->id : null,
            "ledger_receiver" => $receiverLedger,
            "receiver_id" => $receiverLedger ? $receiverLedger->id : null,
            "receiver_name" => $receiverName,
            "receiver_picture" => $receiverPicture
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