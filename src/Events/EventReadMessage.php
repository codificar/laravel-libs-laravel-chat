<?php

namespace Codificar\Chat\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Nahid\Talk\Messages\Message;

class EventReadMessage implements ShouldBroadcast {
	use InteractsWithSockets, SerializesModels;

	private $message;

	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($messageId) {
		$this->message = Message::find($messageId);
	}

	/**
	 * Get the channels the event should broadcast on.
	 *
	 * @return Channel|array
	 */
	public function broadcastOn() {
		return new Channel('conversation.' . $this->message->conversation_id);
	}

	/**
	 * Get the data to broadcast.
	 *
	 * @return array
	 */
	public function broadcastWith() {
		return [
			'message' => $this->message
		];
	}

	/**
	 * The event's broadcast name.
	 *
	 * @return string
	 */
	public function broadcastAs() {
		return 'readMessage';
	}

}
