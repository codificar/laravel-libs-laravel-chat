<?php

namespace Codificar\Chat\Http\Controllers;

use App\Http\Controllers\Controller;
use Codificar\Chat\Http\Requests\AdminGetUserForChatRequest;
use Codificar\Chat\Http\Utils\Helper;
use Provider, DB, Auth;

class AdminChatController extends Controller 
{
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

    public function getUserForChat(AdminGetUserForChatRequest $request)
    {
        $users = [];

        if ($request->type == 'provider') {
            $users = Provider::where(DB::raw('CONCAT_WS(" ", first_name, last_name)'), 'like', '%' . $request->name . '%')
                ->select(
                    'id',
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