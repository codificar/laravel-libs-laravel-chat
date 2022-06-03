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
use Nahid\Talk\Conversations\Conversation;

class SendMessageQuickReplyJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $data;
	protected $requestObj;
	protected $message;
	protected $type;
	protected $quickReply;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($data, $requestObj, $message, $type, $quickReply = [])
	{
		$this->data = $data[0];
		$this->requestObj = $requestObj;
		$this->message = $message;
		$this->type = $type;
		$this->quickReply = $quickReply;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		try {

			$item = $this->data;

			\Talk::setAuthUserId($this->requestObj->sender_id);

			if ($item->ledger_id) {
				$ledgerId = $item->ledger_id;
			} else {
				$ledger = Helper::getLedger($this->type, $item->id);
				$ledgerId = $ledger ? $ledger->id : null;
			}
			if ($ledgerId) {
				$this->requestObj->receiver_id = $ledgerId;

				$conversation = Helper::geOrCreatetConversation($this->requestObj);
				
				$quickReply = $this->insertConversationId($this->quickReply, $conversation->id);
				$message = \Talk::sendMessage($conversation->id, $this->message, json_encode($quickReply));
				//\Log::info($message);
				// if ($this->fileName) {
				// 	$message->picture = $this->fileName;
				// 	$message->save();
				// }
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
			return $data->device_token;
		} catch (\Throwable $th) {
			return null;
		}
	}
	/**
	 * 
	 */
	public function insertConversationId($quickReply, $conversation_id)
	{
		try {
			
			foreach($quickReply['values'] as $key => $qr ) {
				\Log::info($quickReply['values'][$key]);
				$quickReply['values'][$key]['conversation'] = $conversation_id;
			} 
			\Log::info($quickReply);
			

			return $quickReply;
		} catch (\Throwable $th) {
			\Log::error($th->getMessage());
			//return $th->getMessage();
		}
	}
}
