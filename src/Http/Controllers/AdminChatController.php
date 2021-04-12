<?php

namespace Codificar\Chat\Http\Controllers;

use App\Http\Controllers\Controller;
use Codificar\Chat\Http\Requests\AdminGetUserForChatRequest;
use Codificar\Chat\Http\Requests\SendBulkMessageRequest;
use Codificar\Chat\Http\Utils\Helper;
use Codificar\Chat\Jobs\SendBulkMessageJob;
use Provider, DB, Auth;
use stdClass;

class AdminChatController extends Controller 
{
    /**
     * Send bulk messages for users
     * 
     * @param SendBulkMessageRequest $request
     * @return json
     */
    function sendBulkMessage(SendBulkMessageRequest $request)
    {
        $data = Helper::getBulkUserTypeData($request);
        
        $requestObj = new stdClass();
        $requestObj->sender_type = $request->sender_type;
        $requestObj->sender_id = $request->sender_id;

        $fileName = null;

        if ($request->picture) {
            $fileName = str_random(40) . "." . $request->picture->getClientOriginalExtension();
            $request->picture->move(public_path() . "/uploads", $fileName);
        }

        foreach ($data as $item) {
            SendBulkMessageJob::dispatch($item, $requestObj, $request->message, $fileName);
        }
        
        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Render blade view for admin chat
     * 
     * @return view
     */
    public function renderAdminChat()
    {
        $user = Auth::guard('web')->user($id = null);

        if (!$user)
            return \Redirect::to("/admin/login");

        $ledger = Helper::getLedger('admin', $user->id);

        return view('chat::direct_chat', [
            'environment' => 'admin',
            'user' => $user,
            'ledger_id' => $ledger ? $ledger->id : null,
            'user_id' => $id,
            'new_conversation' => null,
            'conversation_id' => $id
        ]);
    }

    /**
     * Get specific user to send message
     * 
     * @param AdminGetUserForChatRequest $request
     * @return json
     */
    public function getUserForChat(AdminGetUserForChatRequest $request)
    {
        $users = [];

        if ($request->type == 'provider') {
            $users = Provider::where(DB::raw('CONCAT_WS(" ", first_name, last_name)'), 'like', '%' . $request->name . '%')
                ->leftJoin('ledger', 'provider.id', '=', 'ledger.provider_id')
                ->select(
                    'ledger.id as id',
                    DB::raw('CONCAT_WS(" ", first_name, last_name) as name'),
                    'picture'
                )
                ->limit(10)
                ->get();
        }

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }
}