<?php

namespace Codificar\Chat\Models;

class Conversations extends \Eloquent
{
    protected $guarded = ['id'];
	protected $table = 'conversations';

	/**
	 * Finds one row in the Messages table associated with 'help_id'
	 *
	 * @return Messages object
	 */
	public function messages()
	{
		return $this->hasMany(Messages::class, 'conversation_id', 'id');
	}

	/**
	 * Finds one row in the Messages table associated with 'help_id'
	 *
	 * @return Messages object
	 */
	public function lastMessageUnread()
	{
		return $this->hasOne(Messages::class, 'conversation_id', 'id')
			->where(['is_seen' => 0])
			->orderBy('created_at', 'desc');
	}
	
}