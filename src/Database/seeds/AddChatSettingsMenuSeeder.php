<?php

use Illuminate\Database\Seeder;

class AddChatSettingsMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permission = \Permission::where('name', 'Messages')->first();

        if ($permission) {
            $permission1 = \Permission::updateOrCreate(
                ['name' => 'chat_settings'],
                [
                    'name' => 'chat_settings',
                    'parent_id' => $permission->id,
                    'is_menu' => 1,
                    'order' => 1003,
                    'url' => '/admin/lib/chat_settings'
                ]
            );

            $admins = \Admin::select('id','profile_id')->get();

            if($admins) {
                $findProfiles = array();

                foreach($admins as $admin) {
                    \AdminPermission::updateOrCreate(
                        ['admin_id' => $admin->id, 'permission_id' => $permission1->id],
                        ['admin_id' => $admin->id, 'permission_id' => $permission1->id]
                    );

                    if ($admin->profile_id && !in_array($admin->profile_id, $findProfiles)) {
                        \ProfilePermission::updateOrCreate(
                            ['profile_id' => $admin->profile_id, 'permission_id' => $permission1->id],
                            ['profile_id' => $admin->profile_id, 'permission_id' => $permission1->id]
                        );
                    }
                }
            }
        }


    }
}