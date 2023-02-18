<?php

namespace Codificar\Chat\Repositories;

use Carbon\Carbon;
use Codificar\Chat\Models\RequestHelp;
use Codificar\Chat\Interfaces\MessageRepositoryInterface;

class MessageRepository implements MessageRepositoryInterface
{
    /**
     * Get All messages help unread
     * @return array
     */
	public function getAllMessagesHelpUnread()
	{
		$query = RequestHelp::select(
            [
                'request_help.id', 
                'request_help.author',
                \DB::raw('CONCAT(p.first_name, " ",  p.last_name) as provider_fullname'),
                \DB::raw('CONCAT(u.first_name, " ",  u.last_name) as user_fullname'), 
                \DB::raw('date_format(request_help.created_at, "%d/%m/%Y %h:%m:%s") AS datetime'), 
                'm.message',
            ])
            ->leftJoin('conversations as c', 'request_help.id', '=', 'c.help_id')
            ->leftJoin('messages as m', 'c.id', '=', 'm.conversation_id')
            ->leftJoin('provider as p', 'p.id', '=', 'request_help.provider_id')
            ->leftJoin('user as u', 'u.id', '=', 'request_help.user_id')
            ->where(['m.is_seen' => 0])
            ->groupBy('help_id')
            ->orderBy('request_help.created_at', 'desc');

        return array(
            'total_unread' => $query->get()->count(),
            'messages' => $query->limit(5)->get()
        );
	} 
    
    /**
     * Get all messages panic todat
     * @return array
     */
    public function getAllMessagesPanicToday() 
    {
        $query = \Codificar\Panic\Models\Panic::select(
            [
                'panic.id', 
                \DB::raw('CONCAT(u.first_name, " ",  u.last_name) as username'), 
                \DB::raw('date_format(panic.created_at, "%d/%m/%Y %h:%m:%s") AS datetime'), 
                'panic.history as message',
            ])
            ->leftJoin('request as r', 'panic.request_id', '=', 'r.id')
            ->leftJoin('user as u', 'r.user_id', '=', 'u.id')            
            ->where('panic.created_at', '=', Carbon::today()->toDateString())
            ->groupBy('id')
            ->orderBy('panic.created_at', 'desc');

        return array(
            'total_unread' => $query->get()->count(),
            'messages' => $query->limit(5)->get()
        );
    }
}