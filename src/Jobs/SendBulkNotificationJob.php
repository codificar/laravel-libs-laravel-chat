<?php

namespace Codificar\Chat\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class SendBulkNotificationJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $message;
	protected $quickReply;

    public function tags() 
    {
        return ['bulkmessage'];
    }

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($data, $message, $quickReply = false)
	{
		$this->data = $data;
		$this->message = $message;
		$this->quickReply = $quickReply;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
        send_android_push($this->data, trans('laravelchat::laravelchat.new_message'), $this->message, $this->quickReply);
	}
}
