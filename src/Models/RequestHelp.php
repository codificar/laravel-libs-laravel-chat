<?php

namespace Codificar\Chat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Nahid\Talk\Conversations\Conversation;

class RequestHelp extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'request_help';

    /**
     * Save a new request note help
     * @param object $request
     * @return boolean
     */
    public function saveHelpNote ($request)
    {
        try {
            $note = new RequestHelp();

            $note->request_id = $request->request_id;
            $note->user_id = $request->ride->user_id;
            $note->provider_id = $request->ride->confirmed_provider;
            $note->save();
            return true;
        } catch (\Throwable $th) {
            \Log::error($th->getMessage() . $th->getTraceAsString());
            return false;
        }
        
    }

    /**
     * Query to search registers
     */
    public static function search(
        $requestId = '',
        $userId = '',
        $providerId = ''
    ) {
        $query = self::query();
        $query = $query->leftJoin(
            'user as u', 
            'request_help.user_id', '=', 'u.id'
        )
        ->leftJoin(
            'provider as p', 
            'request_help.provider_id', '=', 'p.id'
        );

        if ($requestId)
            $query = $query->where('request_id', $requestId);

        if ($userId)
            $query = $query->where('u.id', $userId);

        if ($providerId)
            $query = $query->where('p.id', $providerId);

        return $query->select(
            'request_help.id',
            'request_id',
            'author',
            DB::raw("CONCAT(u.first_name,' ',u.last_name) AS user_name"),
            DB::raw("CONCAT(p.first_name,' ',p.last_name) AS provider_name")
        );
    }

    /**
     * Fetch and paginate registers
     * @param int $page
     * @param object $filter
     * @return array
     */
    public static function fetch($page, $filter)
    {
        $recordsTotal = self::whereNotNull('id')->count();

        $data = self::search(
            $filter->request_id,
            $filter->user_id,
            $filter->provider_id
        );

        $currentPage = $page;

		Paginator::currentPageResolver(function () use ($currentPage) {
			return $currentPage;
        });

        return [
			'records_total' => $recordsTotal,
			'records_filtered' => $data->count(),
			'request_help' => $data->paginate(20)
		];
    }

    /**
     * Get conversation of help notes
     * @param object $request
     * @return Conversation
     */
    public static function getConversation($request)
    {
        if ($request->sender_type == 'admin') {
            $conv = Conversation::find($request->conversation_id);
        } else {
            $conv = Conversation::whereUserOne($request->sender_id)
                ->whereRequestId($request->request_id)
                ->whereNotNull('help_id')
                ->first();
        }

        if ($conv)
            return $conv;
        
        $note = new RequestHelp();
        $note->author = $request->sender_type;
        $note->user_id = $request->ride->user_id;
        $note->request_id = $request->request_id;

        if ($request->ride->confirmed_provider) {
            $note->provider_id = $request->ride->confirmed_provider;
        }

        $note->save();
        
        $conv = new Conversation();
        $conv->user_one = $request->sender_id;
        $conv->user_two = 0;
        $conv->request_id = $request->request_id;
        $conv->help_id = $note->id;
        $conv->status = 1;
        $conv->save();

        return $conv;
        
    }

    /**
     * Get help note messages
     * @param object $request
     * @return array
     */
    public static function getMessages($request)
    {
        if ($request->sender_type == 'admin') {
            $conv = Conversation::find($request->conversation_id);
        } else {
            $conv = Conversation::whereUserOne($request->sender_id)
                ->whereRequestId($request->request_id)
                ->whereNotNull('help_id')
                ->first();
        }
        
        return $conv ? $conv->messages : [];
    }

    public static function getAllMessagesHelpUnread() 
    {
        $query = self::select(
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

}
