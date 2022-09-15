<?php

namespace Codificar\Chat\Http\Requests;

use Codificar\Chat\Http\Utils\Helper;
use Codificar\Chat\Models\ConversationRequest;
use Illuminate\Foundation\Http\FormRequest;
use Requests;

class SendMessageRequest extends FormRequest
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
			"receiver_id"		=> "required|integer",
			"message"			=> "string".($this->type == "text"?"|required":""),
			"bid"				=> "numeric".($this->type == "bid"?"|required":""),
			"type"				=> "in:text,bid",
			"ride"				=> $this->userType == "admin" || $this->userType == "corp"  
				? "|required" 
				: ""
		];
    }
    
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation() {
		try {
			$sender_type = request()->segments()[2];
	
			if ($this->userType) {
				$sender_type = $this->userType;
			}
			$this->is_admin = 0;
			$ride = Requests::find($this->request_id);
			$isProvider = false;
			
			ConversationRequest::getOrCreateConversationChat($this->request_id);
	
			if ($ride) {
				if ($sender_type == "provider") {
					$ledgerSender = Helper::getLedger($sender_type, $this->provider->id);
					$ledgerReceiver = Helper::getLedger('user', $ride->user_id);
					$sender_id = $ledgerSender->id;
					$user = $ledgerReceiver->id;
					$provider = $sender_id;
					$isProvider = true;
				} elseif ($sender_type == "corp") {
					$this->is_admin = 1;
					$ledgerSender = Helper::getLedger('user', $ride->user_id);
					$ledgerReceiver = Helper::getLedger('provider', $ride->confirmed_provider);
					$sender_id = $ledgerSender->id;
					$user = $sender_id;
					$provider = $ledgerReceiver->id;

				} else {
					$ledgerSender = Helper::getLedger('user', $ride->user_id);
					$ledgerReceiver = Helper::getLedger('provider', $ride->confirmed_provider);
					$sender_id = $ledgerSender->id;
					$user = $sender_id;
					$provider = $ledgerReceiver->id;
				}

				\Talk::setAuthUserId($sender_id);

				$this->merge([
					"ride" => $ride,
					"sender_type" => $sender_type,
					"sender_id" => $sender_id,
					"user_id" => $user,
					"provider_id" => $provider,
					"ledger_receiver" => $ledgerReceiver,
					"receiver_id" => $ledgerReceiver->id,
					'is_admin' => $this->is_admin,
					'is_provider' => $isProvider
				]);
			
			}
		} catch (\Exception $e) {
			\Log::error($e);
            \Log::info('SendMessageRequest > prepareForValidation > error: ' . $e->getMessage());
        }
			
	}
}