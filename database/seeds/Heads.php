<?php

use Illuminate\Database\Seeder;

class Heads extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            PaymentTypeAutomationHeadSeeder::class,
            PaymentTypeCollectionHeadSeeder::class,
            PaymentTypeInstallmentHeadSeeder::class,
            PaymentTypeMainHeadSeeder::class,
            PaymentTypePartialHeadSeeder::class,
            RoleTableSeeder::class,
            PermissionTableSeeder::class,
            PmsBookingStatusHeadSeeder::class,
            UserActivitiesNamesSeeder::class,
            AssignRoleToPermission::class,
            PMSSeeder::class,
            PaymentGatewaySeeder::class,
            BookingSourceFormSeeder::class,
            PaymentTypePivotTableSeeder::class,
            PreferencesFormSeeder::class,
            MorePreferencesFormSeeder::class,
            AdminSeeder::class,
            GeneralPreferencesFormSeeder::class,
            ClientPermissionsNewTableSeeder::class,
        ]);
    }
}
