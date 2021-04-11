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

    public function tags() 
    {
        return ['bulkmessage'];
    }

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($data, $requestObj, $message)
	{
		$this->data = $data;
		$this->requestObj = $requestObj;
		$this->message = $message;
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
                $this->requestObj->receiver_id = $item->ledger_id;
                
                $conversation = Helper::geOrCreatetConversation($this->requestObj);
                $message = \Talk::sendMessage($conversation->id, $this->message);
    
                Helper::sendNotificationMessageReceived(
                    trans('laravelchat::laravelchat.new_message'), 
                    $message->conversation_id, 
                    $message->message, 
                    $item->id, 
                    'provider'
                );
            }
		} catch (Exception $e) {
			Log::error($e);
		}
	}
}
