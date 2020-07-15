<?php

use App\PmsForm;
use App\PmsParent;
use Illuminate\Database\Seeder;

/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 1/17/19
 * Time: 3:22 PM
 */

class PMSSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PmsForm::truncate();
        PmsParent::truncate();

        $smxPms = PmsParent::create(
            [
                'logo' => null,
                'name' => 'SiteMinder',
                'backend_name' => '',
                'status' => 1,
                'page_configuration' => 'parent_smx.json'
            ]
        );
        
        $pms_names = [
            ['logo' => 'ba.png', 'name' => 'Booking Automation', 'backend_name' => 'ba_pms_form', 'pms_parent_id' => 0, 'status' => '1', 'priority' => 1, 'page_configuration' => 'booking_automation.json', 'instruction_page' => 'ba_integration_instructions'],
            ['logo' => NULL, 'name' => 'Little Hotelier', 'backend_name' => 'lh_pms_form', 'pms_parent_id' => $smxPms->id, 'status' => '1', 'priority' => 2, 'page_configuration' => 'little_hotelier.json', 'instruction_page' => ''],
            ['logo' => NULL, 'name' => 'Mews', 'backend_name' => 'me_pms_form', 'pms_parent_id' => 0, 'status' => '1', 'priority' => 3, 'page_configuration' => '', 'instruction_page' => ''],
            ['logo' => NULL, 'name' => 'Hotel Spider', 'backend_name' => 'hs_pms_form', 'pms_parent_id' => 0, 'status' => '1', 'priority' => 4, 'page_configuration' => '', 'instruction_page' => ''],
            ['logo' => NULL, 'name' => 'Protel', 'backend_name' => 'pr_pms_form', 'pms_parent_id' => 0, 'status' => '1', 'priority' => 5, 'page_configuration' => '', 'instruction_page' => ''],
            ['logo' => 'beds24.png', 'name' => 'Beds24', 'backend_name' => 'beds_pms_form', 'pms_parent_id' => 0, 'status' => '1', 'priority' => 6, 'page_configuration' => 'booking_automation.json', 'instruction_page' => 'beds24_integration_instructions'],
            ['logo' => NULL, 'name' => 'Booking Factory', 'backend_name' => 'bf_pms_form', 'pms_parent_id' => 0, 'status' => '1', 'priority' => 7, 'page_configuration' => '', 'instruction_page' => ''],
            ['logo' => NULL, 'name' => 'Clock', 'backend_name' => 'cl_pms_form', 'pms_parent_id' => 0, 'status' => '1', 'priority' => 8, 'page_configuration' => '', 'instruction_page' => ''],
            ['logo' => NULL, 'name' => 'Kigo', 'backend_name' => 'ki_pms_form', 'pms_parent_id' => 0, 'status' => '1', 'priority' => 9, 'page_configuration' => '', 'instruction_page' => ''],
            ['logo' => NULL, 'name' => 'Guesty', 'backend_name' => 'gu_pms_form', 'pms_parent_id' => 0, 'status' => '1', 'priority' => 10, 'page_configuration' => '', 'instruction_page' => ''],
        ];

        foreach ($pms_names as $key => $value) {
            PmsForm::create([
                'logo' => $value['logo'],
                'name' => $value['name'],
                'backend_name' => $value['backend_name'],
                'status' => $value['status'],
                'priority' => $value['priority'],
                'instruction_page' => $value['instruction_page'],
                'pms_parent_id' => $value['pms_parent_id'],
                'page_configuration' => $value['page_configuration'],
            ]);
        }
    }
}