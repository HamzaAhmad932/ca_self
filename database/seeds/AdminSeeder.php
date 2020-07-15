<?php

use App\User;
use App\UserAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder {

    public function run() {

        $userAccount = UserAccount::create([
            'name' => 'Admin Support',
            'company_logo' => 'no_image.png',
            'account_verified_at' => now()->toDateTimeString(),
            'status' => 1,
            'account_type' => 2
        ]);

        $user = User::create([
            'name' => 'Dev Administrator',
            'email' => 'support@bookingautomation.com',
            'password' => bcrypt('admin'),
            'phone' => '+10000000111',
            'user_account_id' => $userAccount->id,
            'parent_user_id' => 0,
            'email_verified_at' => now()->toDateTimeString(),
            'is_activated' => 1,
            'attempt_tour' => 1
        ]);

        DB::insert("insert into model_has_permissions (permission_id, model_type, model_id) values(:permission_id, :model, :model_id)", [":permission_id"=>31, ":model"=>User::class, ":model_id"=>$user->id]);
        DB::insert("insert into model_has_roles (role_id, model_type, model_id) values(:role_id, :model, :model_id)", [":role_id"=>1, ":model"=>User::class, ":model_id"=>$user->id]);

        $userAccountThomas = UserAccount::create([
            'name' => 'Admin (Thomas)',
            'company_logo' => 'no_image.png',
            'account_verified_at' => now()->toDateTimeString(),
            'status' => 1,
            'account_type' => 2
        ]);

        $user2 = User::create([
            'name' => 'Thomas',
            'email' => 'thomas@bookingautomation.com',
            'password' => bcrypt('admin'),
            'phone' => '+10000000111',
            'user_account_id' => $userAccountThomas->id,
            'parent_user_id' => 0,
            'email_verified_at' => now()->toDateTimeString(),
            'is_activated' => 1,
            'attempt_tour' => 1
        ]);

        DB::insert("insert into model_has_permissions (permission_id, model_type, model_id) values(:permission_id, :model, :model_id)", [":permission_id"=>31, ":model"=>User::class, ":model_id"=>$user2->id]);
        DB::insert("insert into model_has_roles (role_id, model_type, model_id) values(:role_id, :model, :model_id)", [":role_id"=>1, ":model"=>User::class, ":model_id"=>$user2->id]);

    }

}