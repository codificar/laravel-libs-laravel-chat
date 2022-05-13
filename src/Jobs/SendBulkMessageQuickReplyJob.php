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

class SendBulkMessageQuickReplyJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $requestObj;
    protected $message;
    protected $fileName;
    protected $type;
	protected $quickReply;
	protected $delivery_package_id;
	protected $type_quick_reply;
    public function tags() 
    {
        return ['bulkmessage'];
    }

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($data, $requestObj, $message, $fileName, $type, $quickReply = false, $delivery_package_id = null, $type_quick_reply = 0)
	{
		$this->data = $data;
		$this->requestObj = $requestObj;
		$this->message = $message;
		$this->fileName = $fileName;
		$this->type = $type;
		$this->quickReply = $quickReply;
		$this->delivery_package_id = $delivery_package_id;
		$this->type_quick_reply = $type_quick_reply;
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
				\Log::info($ledgerId);
				if ($ledgerId) {
					$this->requestObj->receiver_id = $ledgerId;
					
					$conversation = Helper::geOrCreatetConversation($this->requestObj);
					
					$message = \Talk::sendMessage($conversation->id, $this->message, $this->type_quick_reply, $this->delivery_package_id);
					\Log::info($message);
					if ($this->fileName) {
						$message->picture = $this->fileName;
						$message->save();
					}
				}
            }

			SendBulkNotificationJob::dispatch($this->parseDeviceTokens($this->data), $this->message, $this->quickReply, $this->delivery_package_id);
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
