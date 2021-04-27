<?php

namespace Codificar\Chat\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Requests;

class EventNewConversation implements ShouldBroadcast {
	use InteractsWithSockets, SerializesModels;

	private $request;
	private $conversationId;
	private $receiverId;

	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($request_id, $conversationId, $receiverId) {
		$this->request = \Requests::find($request_id);
		$this->conversationId = $conversationId;
		$this->receiverId = $receiverId;
	}

	/**
	 * Get the channels the event should broadcast on.
	 *
	 * @return Channel|array
	 */
	public function broadcastOn() {
		return new Channel('request.' . $this->request->id);
	}

	/**
	 * Get the data to broadcast.
	 *
	 * @return array
	 */
	public function broadcastWith() {
		return [
			'newConversation' => true,
			'conversation_id' => $this->conversationId,
			'receiver_id' => $this->receiverId
		];
	}

	/**
	 * The event's broadcast name.
	 *
	 * @return string
	 */
	public function broadcastAs() {
		return 'newConversation';
	}

}
