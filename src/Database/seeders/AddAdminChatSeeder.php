<?php


namespace Database\Seeders;
use Illuminate\Database\Seeder;

class AddAdminChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()

    {
        $permission = \Permission::updateOrCreate(
            ['name' => 'Messages'],
            [
                'name' => 'Messages',
                'parent_id' => 2316,
                'order' => 1000,
                'is_menu' => 1,
                'url' => '',
                'icon' => 'mdi mdi-message-text'
            ]
        );

        $permission1 = \Permission::updateOrCreate(
            ['name' => 'chat'],
            [
                'name' => 'chat',
                'parent_id' => $permission->id,
                'is_menu' => 1,
                'order' => 1001,
                'url' => '/admin/lib/chat'
            ]
        );

        $permission2 = \Permission::updateOrCreate(
            ['name' => 'canonical_messages'],
            [
                'name' => 'canonical_messages',
                'parent_id' => $permission->id,
                'is_menu' => 1,
                'order' => 1002,
                'url' => '/admin/lib/canonical_messages'
            ]
        );

        $admins = \Admin::select('id','profile_id')->get();
        
        if($admins && $permission){
            $findProfiles = array();
            foreach($admins as $admin){
                
                if ($admin->profile_id && !in_array($admin->profile_id, $findProfiles)) {
                    $findProfiles = array_merge($findProfiles, array($admin->profile_id));
                    \ProfilePermission::updateOrCreate(
                        ['profile_id' => $admin->profile_id, 'permission_id' => $permission->id],
                        ['profile_id' => $admin->profile_id, 'permission_id' => $permission->id]
                    );
                    \ProfilePermission::updateOrCreate(
                        ['profile_id' => $admin->profile_id, 'permission_id' => $permission1->id],
                        ['profile_id' => $admin->profile_id, 'permission_id' => $permission1->id]
                    );
                    \ProfilePermission::updateOrCreate(
                        ['profile_id' => $admin->profile_id, 'permission_id' => $permission2->id],
                        ['profile_id' => $admin->profile_id, 'permission_id' => $permission2->id]
                    );
                }
            }
        }
    }
}
