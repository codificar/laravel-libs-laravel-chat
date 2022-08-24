<?php

namespace Codificar\Chat\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Nahid\Talk\Messages\Message;

class EventConversation implements ShouldBroadcast {
	use InteractsWithSockets, SerializesModels;

	private $message;

	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($message_id) {
		$this->message = Message::find($message_id);
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

		$isProvider = filter_var($this->message->is_provider, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
		$isAdmin = isset($this->message->admin_id) && !$isProvider;
		$isUser = isset($this->message->user_id) && !$isProvider;

		$conversationId = $this->message->conversation_id;
		$converstaionRequest = \ConversationRequest::where('conversation_id', $conversationId)
			->first();

		if($converstaionRequest) {

			$request = \Requests::find($converstaionRequest->request_id);
			$providerId = $request->confirmed_provider;
			$userId = $request->user_id; 


			if ($isAdmin) {
				$institution = \AdminInstitution::getUserByAdminId($this->message->admin_id);
				$picture = $institution->picture;
				$username = $institution->first_name . ($institution->last_name ? " " . $institution->last_name : '');
			} else if($isUser) {
				$user = \User::find($userId);
				$picture = $user->picture;
				$username = $user->first_name . ($user->last_name ? " " . $user->last_name : '');
			} else if($isProvider) {
				$provider = \Provider::find($providerId);
				$picture = $provider->picture;
				$username = $provider->first_name . ($provider->last_name ? " " . $provider->last_name : '');
			} else {
				$provider = \Provider::find($providerId);
				$picture = $provider->picture;
				$username = $provider->first_name . ($provider->last_name ? " " . $provider->last_name : '');
			}

			$this->message['user_picture'] = $picture;
			$this->message['user_name'] = $username;
		}

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
		return 'newMessage';
	}

}
