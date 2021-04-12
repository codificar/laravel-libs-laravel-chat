<?php

namespace Codificar\Chat\Http\Utils;

use Ledger, User, Provider;
use Nahid\Talk\Conversations\Conversation;
use Illuminate\Pagination\Paginator;

class Helper {

    /**
     * Get ledger by user type and id
     * @param string $type
     * @param int $id
     * @return Ledger
     */
    public static function getLedger($type, $id)
    {
        if ($type == 'corp')
            $type = 'user';

        $type = $type . '_id';
        return self::getOrCreateLedger($type, $id);
    }

    /**
	 * Get or create ledger if it doesn't exist
     * @param string $type
     * @param int $id
	 * @return Ledger
	 */
	public static function getOrCreateLedger($type, $id)
	{
        $ledger = Ledger::where($type, $id)->first();

		if ($ledger)
			return $ledger;

		$ledger = new Ledger;
		$ledger->admin_id = $id;
		$ledger->user_id = null;
		$ledger->provider_id = null;
		$ledger->parent_id = null;
		$ledger->save();

		return $ledger;
    }
    
    /**
     * Get user type instance by ledger id
     * @param int $id
     * @return User/Provider
     */
    public static function getUserTypeInstance($id) 
    {
        $ledger = Ledger::find($id);

        if ($ledger && $ledger->user_id) {
            $data = User::find($ledger->user_id);
            $data->ledger_id = $id;
            $data->full_name = $data->first_name . ' ' . $data->last_name;
            
            return $data;
        } else if ($ledger && $ledger->provider_id) {
            return Provider::find($ledger->provider_id);
        }

        return null;
    }

    /**
     * Get user type instance for bulk messages
     * 
     * @return array
     */
    public static function getBulkUserTypeData($request)
    {
        $data = Provider::select('provider.id', 'provider.email', 'ledger.id as ledger_id')
            ->leftJoin('ledger', 'provider.id', '=', 'ledger.provider_id')
            ->limit(50);

        if ($request->location_id && $request->location_id != '') 
            $data = $data->where('location_id', $request->location_id);

        return $data->get();
    }

    /**
     * Retrieve or create new conversation
     * @param object $request
     * @return Conversation
     */
    public static function geOrCreatetConversation($request)
    {
        $conversation = self::getConversationBySender($request);

        if ($conversation)
            return $conversation;

        $conversation = new Conversation();
        $conversation->user_one = $request->sender_id;
        $conversation->user_two = $request->receiver_id;
        $conversation->request_id = 0;
        $conversation->status = 1;
        $conversation->save();

        return $conversation;
    }

    /**
     * Retrive a conversation by sender
     * @param object $request
     * @return Conversation
     */
    public static function getConversationBySender($request)
    {
        $conversation = null;

        if ($request->sender_type != 'provider') {
            $conversation = Conversation::whereUserOne($request->sender_id)
                ->whereUserTwo($request->receiver_id)
                ->whereRequestId(0)
                ->first();
            
            return $conversation;
        }

        return Conversation::whereUserOne($request->receiver_id)
            ->whereUserTwo($request->sender_id)
            ->whereRequestId(0)
            ->first();
        
    }

    /**
	 * Envia push quando há uma nova mensagem ou proposta
	 * 
	 * @return
	 */
	public static function sendNotificationMessageReceived($title, $conversation_id, $contents, $model_id, $type) {
		try {
			// Send Notification
			$message = array(
				'success' => true,
				'conversation_id' => $conversation_id,
				'message' => $contents
			);
			//envia notificação push
			send_notifications($model_id, $type, $title, $message);
		} catch (\Exception $ex) {
			return $ex->getMessage().$ex->getTraceAsString();
		}
	}

    public static function filterDirectFetch($request)
    {
        $conversations = Conversation::whereRequestId(0)
            ->where('user_one', $request->sender_id)
            ->orWhere('user_two', $request->sender_id)
            ->with(['messages'])
            ->orderBy('updated_at', 'desc');
        
        $currentPage = $request->page;

        Paginator::currentPageResolver(function () use ($currentPage) {
			return $currentPage;
        });

        $data = $conversations->paginate(20);

        return $data->toArray();
    }

    /**
     * Upload message picture
     * 
     * @param object $request
     * @return string
     */
    public static function savePicture($request, $message)
    {
        try {
            if ($request->picture) {

				$fileName = str_random(40) . "." . $request->picture->getClientOriginalExtension();
				$request->picture->move(public_path() . "/uploads", $fileName);

				$message->picture = $fileName;
                $message->save(); 
			}
        } catch (\Throwable $th) {
            \Log::error($th->getMessage());
        }
    }

}