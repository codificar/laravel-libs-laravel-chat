<?php

namespace Codificar\Chat\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Codificar\Chat\Http\Requests\AdminGetUserForChatRequest;
use Codificar\Chat\Http\Requests\SendBulkMessageRequest;
use Codificar\Chat\Http\Utils\Helper;
use Codificar\Chat\Jobs\SendBulkMessageJob;
use Provider, DB, Auth, User, Settings, Admin;
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
            SendBulkMessageJob::dispatch($item, $requestObj, $request->message, $fileName, $request->type);
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
     * Render blade view for chat settings
     * 
     * @return view
     */
    public function renderChatSettings()
    {
        $user = Auth::guard('web')->user($id = null);
        
        if (!$user)
            return \Redirect::to("/admin/login");
        
        $admins = Admin::whereType('admin')->select('id', 'username')->get();
        $defaultAdmin =  $this->getDefaultAdminChat();

        return view('chat::chat_settings', [
            'admins' => json_encode($admins->toArray()),
            'defaultAdmin' => $defaultAdmin
        ]);
    }

    /**
     * Render blade view for chat settings
     * 
     * @return view
     */
    public function saveDefaultAdminSetting(Request $request)
    {
        try {
            $id = $request->id;

            if ($id && $setting = Settings::where('key', 'default_admin_for_chat')->first()) {
                $setting->value = $id;
                $setting->save();
            }

            return response()->json([
                "success" => true
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false
            ]);
        }
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
        } elseif ($request->type == 'user') {
            $users = User::where(DB::raw('CONCAT_WS(" ", first_name, last_name)'), 'like', '%' . $request->name . '%')
                ->leftJoin('ledger', 'user.id', '=', 'ledger.user_id')
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

    /**
     * Get default admin id for chat
     * 
     * @return int 
     */
    public function getDefaultAdminChat()
    {
        $setting = Settings::where('key', 'default_admin_for_chat')->first();

        if ($setting) {
            return $setting->value;
        } else {
            $admin = Admin::whereUsername('root@codificar.com.br')->first();

            if (!$admin)
                $admin = Admin::whereProfileId(4)->first();

            if ($admin) {
                $setting = Settings::updateOrCreate([
                    'key' => 'default_admin_for_chat'
                ], [
                    'key' => 'default_admin_for_chat',
                    'value' => $admin->id
                ]);

                return $setting->value;
            }
        }
    }
}