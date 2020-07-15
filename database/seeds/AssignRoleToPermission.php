<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\RoleAndPermissions;

class AssignRoleToPermission extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
     
        //Permission & Role Assign to SuperAdmin
        
        $role = Role::where('name', 'SuperAdmin')->first();
        $permissions = Permission::all();
        $role->permissions()->sync($permissions);
       

        //Permission & Role Assign to Admin
        $role = Role::where('name', 'Admin')->first();
        $permissions = Permission::whereNotIn('name', ['editUser','deleteUser','updateUser','viewUser'])->get();
        $role->permissions()->sync($permissions);
        
    }
}
