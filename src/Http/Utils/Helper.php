<?php

namespace Codificar\Chat\Http\Utils;

use Ledger, User, Provider, Admin;
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
        if(!isset($id)|| $id <= 0) {
            \Log::error("CHAT getLedger > Invalid Ledger ID: " . $type . "_id: " . $id);
            return new \Exception('Invalid  ' . $type . '_id: ', 400);
        }

        if ($type == 'corp') {
            $type = 'user';
        }
        
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
		$ledger->admin_id = $type == 'admin_id' ? $id : null;
		$ledger->user_id = $type == 'user_id' ? $id : null;
		$ledger->provider_id = $type == 'provider_id' ? $id : null;
		$ledger->parent_id = null;
		$ledger->referral_code = str_random(6);
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

        if ($ledger && $ledger->user_id && ($user = User::find($ledger->user_id))) {
            $user->ledger_id = $id;
            $user->full_name = $user->first_name . ' ' . $user->last_name;
            $user->user_type = 'user';
            
            return $user;
        } else if ($ledger && $ledger->provider_id && ($provider = Provider::find($ledger->provider_id))) {
            $provider->full_name = $provider->first_name . ' ' . $provider->last_name;
            $provider->ledger_id = $id;
            $provider->user_type = 'provider';
            return $provider;
        } else if ($ledger && $ledger->admin_id && ($admin = Admin::find($ledger->admin_id))) {
            $admin->full_name = $admin->name;
            $admin->ledger_id = $id;
            $admin->user_type = 'admin';
            return $admin;
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
        if ($request->type == 'provider') {
            $data = Provider::select('provider.id', 'provider.email', 'provider.device_token', 'provider.device_type', 'ledger.id as ledger_id')
                ->leftJoin('ledger', 'provider.id', '=', 'ledger.provider_id');
        } elseif ($request->type == 'user') {
            $data = User::select('user.id', 'user.email', 'user.device_token', 'user.device_type', 'ledger.id as ledger_id')
                ->leftJoin('ledger', 'user.id', '=', 'ledger.user_id');
        }

        if ($request->location_id && $request->location_id != '') 
            $data = $data->where('location_id', $request->location_id);

        return $data->get()->chunk(250);
    }

    /**
     * Retrieve or create new conversation
     * @param object $request
     * @return Conversation
     */
    public static function getOrCreateConversation($request)
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
        try {
            $conversation = Conversation::whereRaw("request_id = 0 and ((user_one = $request->sender_id and user_two = $request->receiver_id) or (user_one = $request->receiver_id and user_two = $request->sender_id))")
                ->with(['messages'])
                ->orderBy('updated_at', 'desc')
                ->first();
    
            return $conversation;
        } catch (\Throwable $th) {
            return null;
        }
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
        if ($request->sender_type == 'corp') {
            if($request->request_id) {
                $conversations = Conversation::where('request_id', $request->request_id)
                    ->with(['messages'])
                    ->orderBy('updated_at', 'desc');
            } else {
                $conversations = Conversation::where('user_one', $request->sender_id)
                    ->orWhere('user_two', $request->sender_id)
                    ->with(['messages'])
                    ->orderBy('updated_at', 'desc');

            }

        } else {
            $conversations = Conversation::whereRaw("request_id = 0 and (user_one = $request->sender_id or user_two = $request->sender_id)")
                ->with(['messages'])
                ->orderBy('updated_at', 'desc');

        }
        
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
            \Log::error($th->getMessage() . $th->getTraceAsString());
        }
    }

}