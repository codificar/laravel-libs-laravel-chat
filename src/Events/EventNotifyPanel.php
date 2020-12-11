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
			$ledgerId = $this->ledger->id;
			$conversations = Conversation::where('user_one', $ledgerId)
                ->orWhere('user_two', $ledgerId)
                ->with(['messages'])
                ->orderBy('updated_at', 'desc')
				->get();

			$response = [];
		
			foreach ($conversations as $item) {
				$receiver = $item->user_one == $ledgerId ?
					$item->usertwo->provider :
					$item->userone->provider;
	
				$message = $item->messages[count($item->messages) -1];
				$ride = $item['request_id'] == 0 ? '' : ' #' . $item['request_id'];
				
				$data = [
					'id' => $receiver->id,
					'conversation_id' => $item['id'],
					'request_id' => $item['request_id'],
					'first_name' => $receiver->first_name,
					'last_name' => $receiver->last_name,
					'full_name' => $receiver->first_name . ' ' . $receiver->last_name . $ride,
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