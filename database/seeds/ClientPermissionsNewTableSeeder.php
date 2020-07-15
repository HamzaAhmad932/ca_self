<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ClientPermissionsNewTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userPermission = array('charges','authorize','capture','refund');
        foreach ($userPermission as $permission) {
        	Permission::create(['guard_name' => 'client', 'name' => $permission]);
        }
    }
}
