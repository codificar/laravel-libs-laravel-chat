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

    public function tags() 
    {
        return ['bulkmessage'];
    }

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($data)
	{
		$this->data = $data;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		$items = $this->data->toArray();
        send_android_push($items, trans('laravelchat::laravelchat.new_message'), trans('laravelchat::laravelchat.new_message'));
	}
}
