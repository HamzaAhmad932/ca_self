<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class PermissionsNamesUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Permission::where('name', 'syncPropertise')->update(['name'=>'syncProperties']);
        \App\Permission::where('name', 'viewTrascrtion')->update(['name'=>'viewTransaction']);
        \App\Permission::where('name', 'editTrascrtion')->update(['name'=>'editTransaction']);
        \App\Permission::where('name', 'deleteTrascrtion')->update(['name'=>'deleteTransaction']);

//        $permissions = Permission::where('guard_name','client')->whereIn('name', ['charges','authorize','capture','refund'])->get();
//        Role::where('name', 'Administrator')->first()->permissions()->sync($permissions);

        $role = Role::where('name', 'Administrator')->first();
        $permissions = Permission::where('guard_name','client')->get();
        $role->permissions()->sync($permissions);

    }
}
