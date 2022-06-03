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

class SendBulkNotificationJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
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
	public function __construct($data, $message)
	{
		$this->data = $data;
		$this->message = $message;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
        send_android_push($this->data, trans('laravelchat::laravelchat.new_message'), $this->message);
	}
}
