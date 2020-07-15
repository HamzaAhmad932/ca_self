<?php

use Illuminate\Database\Seeder;
use App\User;
use App\UserAccount;

class TestUserAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(UserAccount::class, 110)->create()->each(function ($userAccount) {
            for($i = 0; $i < 5; $i++) {
                $userAccount->users()->save(factory(User::class)->make());
            }
        });
    }
}
