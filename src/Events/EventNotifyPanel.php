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
use Ledger;

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
		$ledger = Ledger::find($id);
		
		if ($ledger) {
			$this->ledger = $ledger;

			$this->id = $ledger->admin_id ? $ledger->admin_id : $ledger->user_id;
		}
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

			if ($this->ledger->admin_id) {
				$conversations = Conversation::whereRaw("request_id = 0 and (user_one = $ledgerId or user_two = $ledgerId)")
					->with(['messages'])
					->limit(20)
					->orderBy('updated_at', 'desc')
					->get();
			} else {
				$conversations = Conversation::where('user_one', $ledgerId)
					->orWhere('user_two', $ledgerId)
					->with(['messages'])
					->limit(20)
					->orderBy('updated_at', 'desc')
					->get();
			}

			$response = [];
		
			foreach ($conversations as $item) {
				$receiver = $item['user_one'] == $ledgerId  ?
					Helper::getUserTypeInstance($item['user_two']) :
					Helper::getUserTypeInstance($item['user_one']);
	
				if ($receiver) {
					$message = $item->messages[count($item->messages) -1];
					$ride = $item['request_id'] == 0 ? '' : ' #' . $item['request_id'];
					
					$data = [
						'id' => $receiver->ledger_id,
						'conversation_id' => $item['id'],
						'request_id' => $item['request_id'],
						'first_name' => $receiver->first_name,
						'last_name' => $receiver->last_name,
						'full_name' => $receiver->full_name . $ride,
						'picture' => $receiver->picture,
						'last_message' => $message->message,
						'time' => $message->humans_time,
						'messages' => $item['messages']
					];
		
					$response[] = $data;
				}
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