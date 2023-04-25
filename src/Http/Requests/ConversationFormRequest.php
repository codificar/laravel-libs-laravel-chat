<?php

namespace Codificar\Chat\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConversationFormRequest extends FormRequest
{
	private const SEGMENT_API 			= 1;
	private const SEGMENT_LIBS 			= 2;
	private const SEGMENT_ADMIN 		= 3;
	private const SEGMENT_CHAT 			= 4;
	private const SEGMENT_CONVERSATION 	= 5;

	private const ADMIN 	= 'admin';
	private const CORP 		= 'corp';
	private const PROVIDER 	= 'provider';
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
			"request_id" => "integer".(($this->sender_type == "admin" || $this->sender_type == "corp") ?"|required":"")
		];
	}

	/**
     * Prepare the data for validation.
     *
     * @return void
     */
	protected function prepareForValidation() {
		// get sender type admin
		$senderType = request()->segment(self::SEGMENT_ADMIN);
		if($this->userType) {
			$senderType = $this->userType;
		}

		$this->merge([
			"sender_type" => $senderType,
			'ledger_id' => $this->getLedgerId($senderType),
			'user' => $this->user
		]);
	}

	/**
	 * Get ledger id by provider, admin or corp
	 * @param string $senderType
	 * @return int|null $ledgerId
	 */
	private function getLedgerId($senderType)
	{
		$ledgerId = null;
		
		switch ($senderType) {
			case self::PROVIDER:
				$ledgerId = $this->provider->ledger->id;
				break;
			case self::ADMIN:
			case self::CORP:
				$ride = \Requests::find($this->request_id);			
				if($ride) {
					$this->user = $ride->user;
					$ledgerId = $this->user->ledger->id;
				}
				break;
			default:
				if($this->user){
					$ledgerId = $this->user->ledger->id;
				}
				break;
		}
		return $ledgerId;
	}
}
