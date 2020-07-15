<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\RoleAndPermissions;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        foreach (RoleAndPermissions::$adminPermission as $permission) {
        	Permission::create(['guard_name' => 'admin', 'name' => $permission]);
        }

        foreach (RoleAndPermissions::$userPermission as $permission) {
        	Permission::create(['guard_name' => 'client', 'name' => $permission]);
        }
    }
}
