<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\User;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRole;

class MigrateUserRoles extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $softDeletedUserIds = User::onlyTrashed()->get()->pluck('id');

        if (!empty($softDeletedUserIds) && $softDeletedUserIds != '' && $softDeletedUserIds != null) {
            $updateUsers = User::whereIn('id', $softDeletedUserIds)->restore();
        }

        $allUsers = User::all();

        if (!empty($allUsers) && $allUsers != '' && $allUsers != null) {
            foreach ($allUsers as $user) {
                $roles = $user->hasRole(array(6,7,8), 'client');
                if ($roles) {
                    $role_ids = $user->roles->pluck('id');
                    foreach ($role_ids as $role_id) {
                        $user->removeRole($role_id);
                    }
                    $user->assignRole(5);
                }
            }
        }

        $getUserRoles = DB::table('model_has_roles')
            ->whereIn('role_id', array(3, 4, 5))
            ->get();

        if (!empty($getUserRoles) && $getUserRoles != '' && $getUserRoles != null) {

            $getAllUsers = User::whereIn('id', $getUserRoles->pluck('model_id')->toArray())->get();

            if (!empty($getAllUsers) && $getAllUsers != '' && $getAllUsers != null) {
                foreach ($getUserRoles as $userRole) {

                    $user = $getAllUsers->where('id', $userRole->model_id)->first();

                    if ($userRole->role_id == 3) {
                        $permissions = Permission::where('guard_name','client')->get();
                        $user->givePermissionTo($permissions);
                    } else if ($userRole->role_id == 4) {
                        $permissions = Permission::where('guard_name','client')->whereNotIn('name',['full client'])->get();
                        $user->givePermissionTo($permissions);
                    } else if ($userRole->role_id == 5) {
                        $user->givePermissionTo(['viewBooking', 'viewProperty']);
                    }

                }
            }

        }

        if (!empty($softDeletedUserIds) && $softDeletedUserIds != '' && $softDeletedUserIds != null) {
            foreach ($softDeletedUserIds as $key => $softDeletedUserId) {
                $updateUsers = User::where('id', $softDeletedUserId)->update(['deleted_at' => now()->addSeconds($key+1)]);
            }
        }

        $permissions = Permission::where('guard_name','client')->get()->pluck('id');
        $role = Role::where('id', 3)->first();
        $deletePermissions = $role->revokePermissionTo($permissions);
        $role = Role::where('id', 4)->first();
        $deletePermissions = $role->revokePermissionTo($permissions);
        $role = Role::where('id', 5)->first();
        $deletePermissions = $role->revokePermissionTo($permissions);
        $role = Role::where('id', 6)->first();
        $deletePermissions = $role->revokePermissionTo($permissions);
        $role = Role::where('id', 7)->first();
        $deletePermissions = $role->revokePermissionTo($permissions);
        $role = Role::where('id', 8)->first();
        $deletePermissions = $role->revokePermissionTo($permissions);
        Schema::disableForeignKeyConstraints();
        $removeRole = Role::whereIn('id', array(6,7,8))->delete();
        $updateRole = Role::where('id', 3)->update(['name' => 'Administrator']);
        $updateRole = Role::where('id', 4)->update(['name' => 'Manager']);
        $updateRole = Role::where('id', 5)->update(['name' => 'TeamMember']);
        Schema::enableForeignKeyConstraints();

    }
}
