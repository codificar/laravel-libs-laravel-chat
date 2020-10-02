<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetPermissionRequestHelp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permission = \Permission::updateOrCreate(
            ['name' => 'RequestHelp'],
            [
                'name' => 'RequestHelp',
                'parent_id' => 2316,
                'order' => 1200,
                'is_menu' => 1,
                'url' => '/admin/libs/help_report',
                'icon' => 'fa fa-bullhorn'
            ]
        );

        $admins = \Admin::select('id','profile_id')->get();
        
        if($admins && $permission){
            $findProfiles = array();
            foreach($admins as $admin){
                \AdminPermission::updateOrCreate(
                    ['admin_id' => $admin->id, 'permission_id' => $permission->id],
                    ['admin_id' => $admin->id, 'permission_id' => $permission->id]
                );
                
                if ($admin->profile_id && !in_array($admin->profile_id, $findProfiles)) {
                    $findProfiles = array_merge($findProfiles, array($admin->profile_id));
                    \ProfilePermission::updateOrCreate(
                        ['profile_id' => $admin->profile_id, 'permission_id' => $permission->id],
                        ['profile_id' => $admin->profile_id, 'permission_id' => $permission->id]
                    );
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
