<?php

namespace Codificar\Chat\Repositories;

use Carbon\Carbon;
use Codificar\Chat\Models\RequestHelp;
use Codificar\Chat\Interfaces\MessageRepositoryInterface;
use Codificar\Chat\Models\Messages;

class MessageRepository implements MessageRepositoryInterface
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
            ->orderBy('request_help.id', 'desc');

        return array(
            'total_unread' => $query->get()->groupBy('message_id')->count(),
            'messages' => $query->limit(5)->groupBy('id')->get()
        );
	} 
    
    /**
     * Get all messages panic todat
     * @return array
     */
    public function getAllMessagesPanicToday(): array
    {
        $query = \Codificar\Panic\Models\Panic::select(
            [
                'panic.id', 
                \DB::raw('CONCAT(u.first_name, " ",  u.last_name) as username'), 
                \DB::raw('date_format(panic.created_at, "%d/%m/%Y %h:%m:%s") AS datetime'), 
                'panic.history as message',
                'panic.request_id as request_id'
            ])
            ->leftJoin('request as r', 'panic.request_id', '=', 'r.id')
            ->leftJoin('user as u', 'r.user_id', '=', 'u.id')
            ->where(['panic.is_seen' => 0])            
            ->whereBetween('panic.created_at', [Carbon::today()->toDateTimeString(), Carbon::tomorrow()->toDateTimeString()])
            ->groupBy('id')
            ->orderBy('panic.created_at', 'desc');
        return array(
            'total_unread' => $query->get()->count(),
            'messages' => $query->limit(5)->get()
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