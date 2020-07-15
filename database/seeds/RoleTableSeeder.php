<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\RoleAndPermissions;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        foreach (RoleAndPermissions::$adminRoles as $role) {
        	Role::create(['guard_name' => 'admin', 'name' => $role]);
        }

        foreach (RoleAndPermissions::$userRoles as $role) {
        	Role::create(['guard_name' => 'client', 'name' => $role]);
        }
        
    }
}
