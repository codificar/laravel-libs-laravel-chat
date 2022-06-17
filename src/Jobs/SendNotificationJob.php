<?php

namespace Codificar\Chat\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendNotificationJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $device_token;
    protected $message;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($device_token, $message)
	{
		$this->device_token = $device_token;
		$this->message = $message;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
        send_android_push($this->device_token, $this->message,  $this->message); 
	}
}
