<?php

namespace Codificar\Chat\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Nahid\Talk\Messages\Message;

class EventNotifyPanel implements ShouldBroadcast {
    use InteractsWithSockets, SerializesModels;

    public $id;
    /**
	 * Create a new event instance.
	 *
	 * @return void
	 */
    public function __construct($id) 
    {
		$this->id = $id;
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
	public function broadcastWith() {
		return [];
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