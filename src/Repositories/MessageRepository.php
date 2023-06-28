<?php

namespace Codificar\Chat\Repositories;

use Codificar\Chat\Models\RequestHelp;
use Codificar\Chat\Models\Messages;

class MessageRepository
{
    /**
     * Get All messages help unread
     * @return array
     */
	public function getAllMessagesHelpUnread(): array
	{
		$query = RequestHelp::select(
            [
                'request_help.id',
                'm.id as message_id',
                'c.id as conversation_id',
                'request_help.author',
                \DB::raw('CONCAT(p.first_name, " ",  p.last_name) as provider_fullname'),
                \DB::raw('CONCAT(u.first_name, " ",  u.last_name) as user_fullname'), 
                'm.created_at AS datetime', 
                'm.message',
            ])
            ->join('conversations as c', 'request_help.id', '=', 'c.help_id')
            ->join('messages as m', 'c.id', '=', 'm.conversation_id' )
            ->leftJoin('provider as p', 'p.id', '=', 'request_help.provider_id')
            ->leftJoin('user as u', 'u.id', '=', 'request_help.user_id')
            ->where(['m.is_seen' => 0])
            ->whereNull('u.deleted_at')
            ->whereNull('p.deleted_at')
            ->orderBy('request_help.id', 'desc');

        return array(
            'total_unread' => $query->get()->groupBy('message_id')->count(),
            'messages' => $query->limit(5)->groupBy('id')->get()
        );
	}

    /**
     * Get All messages help unread
     * @param int $requestHelpId
     * @return array
     */
	public function getMessageHelpById(int $requestHelpId): array
	{
		$query = RequestHelp::select(
            [
                'request_help.id', 
                'm.id as message_id', 
                'request_help.author',
                \DB::raw('CONCAT(p.first_name, " ",  p.last_name) as provider_fullname'),
                \DB::raw('CONCAT(u.first_name, " ",  u.last_name) as user_fullname'), 
                \DB::raw('date_format(m.created_at, "%d/%m/%Y %h:%m:%s") AS datetime'), 
                'm.created_at AS datetime',
                'm.message',
            ])
            ->leftJoin('conversations as c', 'request_help.id', '=', 'c.help_id')
            ->leftJoin('messages as m', 'c.id', '=', 'm.conversation_id')
            ->leftJoin('provider as p', 'p.id', '=', 'request_help.provider_id')
            ->leftJoin('user as u', 'u.id', '=', 'request_help.user_id')
            ->where(['request_help.id' => $requestHelpId])
            ->where(['m.is_seen' => 0])
            ->whereNull('u.deleted_at')
            ->whereNull('p.deleted_at')
            ->orderBy('request_help.id', 'desc');

        return array(
            'total_unread' => $query->get()->groupBy('message_id')->count(),
            'messages' => $query->limit(5)->groupBy('id')->get()
        );
	} 

    /**
     * set al messages as read by conversation and/or user
     * @param int $conversationId
     * @param int $messageId
     * @param int $userId - default null
     * 
     * @return void
     */
    public function setMessagesAsSeen(int $conversationId, int $messageId, int $userId = null): void
    {
        $messages = Messages::where('conversation_id', $conversationId)
			->where('id', '<=', $messageId);
        if($userId) {
			$messages->where('user_id', '<>', $userId);
        }
			$messages->update(['is_seen' => true]);
    }
}