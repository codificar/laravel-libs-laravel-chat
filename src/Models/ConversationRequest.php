<?php

namespace Codificar\Chat\Models;

use Requests;

class ConversationRequest extends \Eloquent
{
    protected $guarded = ['id'];
	protected $table = 'conversation_request';
	
	/**
	 * Conversation relationship
	 */
	public function conversation() {
		return $this->belongsTo('Nahid\Talk\Conversations\Conversation');
	}

	/**
	 * Request relationship
	 */
	public function request() {
		return $this->belongsTo(Requests::class);
    }

	/**
	 * Send new message
	 * @param int $receiverId
	 * @param string $message
	 * @return Message
	 */
    public function sendMessage($receiverId, $message) {
		$message = \Talk::sendMessageByUserId($receiverId, $message, $this->request_id);
		if(!$this->conversation_id) {
			$this->conversation_id = $message->conversation_id;
			$this->save();
		}
		return $message;
	}
    
    /**
	 * Find or create a conversation request
	 * @param int $requestId
	 * @param int $userId
	 * @return ConversationRequest
	 */
	public static function findConversation($requestId, $userId) {
		
		$query = self::getQueryUser($userId);
		$query->where('conversation_request.request_id', $requestId);

		$convRequest = $query->first();
		
		if(!$convRequest) {
			$convRequest = new ConversationRequest;
			$convRequest->request_id = $requestId;
		}
		return $convRequest;
    }
    
    /**
	 * Query to retrieve messages by user_id
	 */
	private static function getQueryUser($user_id) {
		return self::select('conversation_request.*')
				  ->where(function ($q) use ($user_id) {
						$q->where('c.user_two', $user_id)
				  		->orWhere('c.user_one', $user_id);
			})
			->join('conversations as c', 'c.id', 'conversation_request.conversation_id');
	}

    /**
	 * Find or create a conversation request
	 * @param int $request_id
	 * @param int $sender
	 * @return array 
	 */
	public static function getConversations($request_id, $sender) {
		$query = self::getQueryUser($sender);		
		self::loadData($query);
		$query->where('conversation_request.request_id', $request_id);
		return $query->get();
	}

	/**
	 * Query to get data from conversation
	 */
	private static function loadData($query) {
		$query->with(['conversation.messages' => function ($query) {
			$query->orderBy('created_at', 'desc');
		}])->with(['request:id', 'conversation.userone', 'conversation.usertwo']);
	}

	/**
	 * Query to get data from conversation
	 */
	public static function getInbox($userId) {
		$query = self::getQueryUser($userId);
		self::loadData($query);
		return $query->get();
	}

	/**
	 * Count messages messages
	 * @param object $messages
	 * @param int $userId
	 * @return int
	 */
	public static function countMessagesUnseen($messages, $userId){
		return $messages->filter(function($message) use ($userId) {
			return !$message->is_seen and $message->user_id != $userId;
		})->count();
	}

	/**
	 * Retrieve messages by conversation id
	 * @return Messages
	 */
	public static function getMessagesByConversationId($conversationId, $limit = null, $offset = null) {
		$conversationRequest = self::where('conversation_id', $conversationId)->with('conversation.messages')->first();
		if($conversationRequest){
			$messages = $conversationRequest->conversation->messages;
		}
		if ($limit and $messages) {
			$messages = $messages->take(-$limit);
		}

		$requestId = self::where('conversation_id', $conversationId)
			->first()
			->request_id;		
			
		if($requestId) {
			$request = Requests::find($requestId);
			// verificar se as fotos e o username estão ok
			foreach($messages as $message) {
				if (!$message['picture'] && $message['user_id']) {
					$providerId = $request->confirmed_provider;
					$userId = $request->user_id;

					$isProvider = filter_var($message['is_provider'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

					if($message['admin_id'] && !$isProvider) {
						$institution = \AdminInstitution::getUserByAdminId($message['admin_id']);
						$picture = $institution->picture;
						$username = $institution->first_name . ($institution->last_name ? " " . $institution->last_name : '');
					} else if($message['user_id'] && !$isProvider) {
						$user = \User::find($userId);
						$picture = $user->picture;
						$username = $user->first_name . ($user->last_name ? " " . $user->last_name : '');
					} else if($message['user_id'] && $isProvider) {
						$provider = \Provider::find($providerId);
						$picture = $provider->picture;
						$username = $provider->first_name . ($provider->last_name ? " " . $provider->last_name : '');
					} else {
						$provider = \Provider::find($providerId);
						$picture = $provider->picture;
						$username = $provider->first_name . ($provider->last_name ? " " . $provider->last_name : '');
					}
					
					$message['user_picture'] = $picture;
					$message['user_name'] = $username;
				}
			}
		}

		return $messages;
	}

	/**
	 * Retrieve conversation by id
	 * @return ConversationRequest
	 */
	public static function getByConversationId($conversationId) {
		return self::where('conversation_id', $conversationId)->first();
	}

	/**
	 * Set a message as seen
	 * @return void
	 */
	public static function setMessagesAsSeen($message, $userId)
	{
		\DB::table('messages')
			->where('conversation_id', $message->conversation_id)
			->where('id', '<=', $message->id)
			->where('user_id', '<>', $userId)
			->update(['is_seen' => true]);
	}
}