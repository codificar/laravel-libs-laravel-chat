<?php

namespace Codificar\Chat\Events;

use Codificar\Chat\Http\Resources\ListDirectConversationResource;
use Codificar\Chat\Http\Utils\Helper;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Nahid\Talk\Messages\Message;
use Nahid\Talk\Conversations\Conversation;

class EventNotifyPanel implements ShouldBroadcast {
    use InteractsWithSockets, SerializesModels;

    public $id;
	public $ledger;
	
    /**
	 * Create a new event instance.
	 *
	 * @return void
	 */
    public function __construct($id) 
    {
		$this->id = $id;

		$ledger = Helper::getLedger('corp', $id);
		
		if ($ledger)
			$this->ledger = $ledger;
    }
    
    /**
	 * Get the channels the event should broadcast on.
	 *
	 * @return Channel|array
	 */
    public function broadcastOn() 
    {
		return new Channel('notifyPanel.' . $this->id);
    }
    
    /**
	 * Get the data to broadcast.
	 *
	 * @return array
	 */
	public function broadcastWith() 
	{
		if ($this->ledger) {
			$conversations = Conversation::whereUserOne($this->ledger->id)
				->whereRequestId(0)
				->with(['usertwo', 'messages'])
				->orderBy('updated_at', 'desc')
				->get();

				$response = [];
		
				foreach ($conversations as $item) {
					$receiver = $item->usertwo->provider;
		
					$message = $item->messages[count($item->messages) -1];
					
					$data = [
						'id' => $receiver->id,
						'first_name' => $receiver->first_name,
						'last_name' => $receiver->last_name,
						'full_name' => $receiver->first_name . ' ' . $receiver->last_name,
						'picture' => $receiver->picture,
						'last_message' => $message->message,
						'time' => $message->humans_time,
						'messages' => $item['messages']
					];
		
					$response[] = $data;
				}

				return [
					'success' => true,
					'conversations' => $response
				];
		}

		return [
			'success' => true,
			'conversations' => []
		];
	}

	/**
	 * The event's broadcast name.
	 *
	 * @return string
	 */
	public function broadcastAs() {
		return 'PanelNewMessage';
	}
}