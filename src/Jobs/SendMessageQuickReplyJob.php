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
use Codificar\Chat\Jobs\SendNotificationJob;

class SendMessageQuickReplyJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $ledgerId;
	protected $requestObj;
	protected $message;
	protected $quickReply;
	protected $device_token;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($ledgerId, $requestObj, $message, $quickReply = [], $device_token)
	{
		$this->ledgerId = $ledgerId;
		$this->requestObj = $requestObj;
		$this->message = $message;
		$this->quickReply = $quickReply;
		$this->device_token = $device_token;
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

			if ($this->ledgerId) {
				$this->requestObj->receiver_id = $this->ledgerId;

				$conversation = Helper::getOrCreateConversation($this->requestObj);
				
				$quickReply = $this->insertConversationId($this->quickReply, $conversation->id);
				$message = \Talk::sendMessage($conversation->id, $this->message, json_encode($quickReply));

			}

			SendNotificationJob::dispatch($this->device_token, $this->message);
		} catch (Exception $e) {
			Log::error($e->getMessage() . $e->getTraceAsString());
		}
	}

	/**
	 * 
	 */
	public function insertConversationId($quickReply, $conversation_id)
	{
		try {
			foreach($quickReply['values'] as $key => $qr ) {
				$quickReply['values'][$key]['conversation'] = $conversation_id;
			} 
			return $quickReply;
		} catch (\Throwable $th) {
			\Log::error($th->getMessage() . $th->getTraceAsString());
		}
	}
}
