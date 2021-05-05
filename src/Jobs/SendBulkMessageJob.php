<?php

namespace Codificar\Chat\Jobs;

use Codificar\Chat\Http\Utils\Helper;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Exception;
use Log;

class SendBulkMessageJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $requestObj;
    protected $message;
    protected $fileName;
    protected $type;

    public function tags() 
    {
        return ['bulkmessage'];
    }

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($data, $requestObj, $message, $fileName, $type)
	{
		$this->data = $data;
		$this->requestObj = $requestObj;
		$this->message = $message;
		$this->fileName = $fileName;
		$this->type = $type;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		try {
            \Talk::setAuthUserId($this->requestObj->sender_id);
            
			foreach ($this->data as $item) {
				if ($item->ledger_id) {
					$ledgerId = $item->ledger_id;
				} else {
					$ledger = Helper::getLedger($this->type, $item->id);
					$ledgerId = $ledger ? $ledger->id : null;
				}

				if ($ledgerId) {
					$this->requestObj->receiver_id = $ledgerId;
					
					$conversation = Helper::geOrCreatetConversation($this->requestObj);
					$message = \Talk::sendMessage($conversation->id, $this->message);
	
					if ($this->fileName) {
						$message->picture = $this->fileName;
						$message->save();
					}
				}
            }

			SendBulkNotificationJob::dispatch($this->parseDeviceTokens($this->data), $this->message);
		} catch (Exception $e) {
			Log::error($e);
		}
	}

	/**
	 * Mount device token array
	 * 
	 * @param Provider $data
	 * @return array
	 */
	public function parseDeviceTokens($data)	
	{
		try {
			$tokens = [];

			foreach($data as $item) {
				array_push($tokens, $item->device_token);
			}

			return $tokens;
		} catch (\Throwable $th) {
			return [];
		}
	}
}
