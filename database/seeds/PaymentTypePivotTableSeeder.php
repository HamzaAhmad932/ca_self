<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 1/28/19
 * Time: 11:47 AM
 */

use App\PaymentTypePivotTable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Lang;

class PaymentTypePivotTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $trans_names = Lang::get('client/transaction_types.transaction_type');
        foreach ($trans_names as $id=>$data){
            PaymentTypePivotTable::where('id', $id)->update([
                'title'=> $data['title'],
                'sys_name'=> $data['sys_name']
            ]);
        }
//        $title_data = [];
//        $title_data[] = ['id' => 1, 'title'=>'Charges'];
//        $title_data[] = ['id' => 2, 'title'=>'Charges'];
//        $title_data[] = ['id' => 3, 'title'=>'Charges'];
//        $title_data[] = ['id' => 4, 'title'=>'Refund'];
//        $title_data[] = ['id' => 5, 'title'=>'Refund'];
//        $title_data[] = ['id' => 6, 'title'=>'Charges'];
//        $title_data[] = ['id' => 7, 'title'=>'Charges'];
//        $title_data[] = ['id' => 8, 'title'=>'Charges'];
//        $title_data[] = ['id' => 9, 'title'=>'Charges'];
//        $title_data[] = ['id' => 10, 'title'=>'Refund'];
//        $title_data[] = ['id' => 11, 'title'=>'Refund'];
//        $title_data[] = ['id' => 12, 'title'=>'Security Deposit Authorization'];
//        $title_data[] = ['id' => 13, 'title'=>'Refund'];
//        $title_data[] = ['id' => 14, 'title'=>'Charges'];
//        $title_data[] = ['id' => 15, 'title'=>'Refund'];
//        $title_data[] = ['id' => 16, 'title'=>'Refund'];
//        $title_data[] = ['id' => 17, 'title'=>'Extra Charges'];
//        $title_data[] = ['id' => 18, 'title'=>'Refund'];
//        $title_data[] = ['id' => 19, 'title'=>'Security Deposit Authorization'];
//        $title_data[] = ['id' => 20, 'title'=>'Security Deposit Authorization'];
//        $title_data[] = ['id' => 21, 'title'=>'Authorization'];
//        $title_data[] = ['id' => 22, 'title'=>'Authorization'];
//        $title_data[] = ['id' => 23, 'title'=>'Refund'];
//
//        foreach ($title_data as $row){
//            PaymentTypePivotTable::where('id', $row['id'])->update(['title'=> $row['title']]);
//        }
//        $data = array();
//        $data[] = array('id' => 1, 'ptmh_id' => 1, 'ptch_id' => 1, 'ptah_id' => 1, 'ptih_id' => 1, 'ptph_id' => 0, 'name' => 'booking-payment-auto-collection-full', 'sys_name' => 'Booking Payment Auto Collection Full', 'title'=>'Charges', 'status' => '1');
//        $data[] = array('id' => 2, 'ptmh_id' => 1, 'ptch_id' => 1, 'ptah_id' => 1, 'ptih_id' => 2, 'ptph_id' => 1, 'name' => 'booking-payment-auto-collection-partial-1of2', 'sys_name' => 'Booking Payment Auto Collection Partial 1of2', 'title'=>'Charges', 'status' => '1');
//        $data[] = array('id' => 3, 'ptmh_id' => 1, 'ptch_id' => 1, 'ptah_id' => 1, 'ptih_id' => 2, 'ptph_id' => 2, 'name' => 'booking-payment-auto-collection-partial-2of2', 'sys_name' => 'Booking Payment Auto Collection Partial 2of2', 'title'=>'Charges', 'status' => '1');
//        $data[] = array('id' => 4, 'ptmh_id' => 1, 'ptch_id' => 1, 'ptah_id' => 2, 'ptih_id' => 1, 'ptph_id' => 0, 'name' => 'booking-payment-auto-refund-full', 'sys_name' => 'Booking Payment Auto Refund Full', 'title'=>'Refund', 'status' => '1');
//        $data[] = array('id' => 5, 'ptmh_id' => 1, 'ptch_id' => 1, 'ptah_id' => 2, 'ptih_id' => 2, 'ptph_id' => 0, 'name' => 'booking-payment-auto-refund-partial', 'sys_name' => 'Booking Payment Auto Refund Partial', 'title'=>'Refund', 'status' => '1');
//        $data[] = array('id' => 6, 'ptmh_id' => 1, 'ptch_id' => 2, 'ptah_id' => 1, 'ptih_id' => 1, 'ptph_id' => 0, 'name' => 'booking-payment-manual-collection-full', 'sys_name' => 'Booking Payment Manual Collection FUll', 'title'=>'Charges', 'status' => '1');
//        $data[] = array('id' => 7, 'ptmh_id' => 1, 'ptch_id' => 2, 'ptah_id' => 1, 'ptih_id' => 2, 'ptph_id' => 0, 'name' => 'booking-payment-manual-collection-partial', 'sys_name' => 'Booking Payment Manual Collection Partial', 'title'=>'Charges', 'status' => '1');
//        $data[] = array('id' => 8, 'ptmh_id' => 1, 'ptch_id' => 2, 'ptah_id' => 1, 'ptih_id' => 2, 'ptph_id' => 1, 'name' => 'booking-payment-manual-collection-partial-1of2', 'sys_name' => 'Booking Payment Manual Collection Partial 1of2', 'title'=>'Charges', 'status' => '1');
//        $data[] = array('id' => 9, 'ptmh_id' => 1, 'ptch_id' => 2, 'ptah_id' => 1, 'ptih_id' => 2, 'ptph_id' => 2, 'name' => 'booking-payment-manual-collection-partial-2of2', 'sys_name' => 'Booking Payment Manual Collection Partial 2of2', 'title'=>'Charges', 'status' => '1');
//        $data[] = array('id' => 10, 'ptmh_id' => 1, 'ptch_id' => 2, 'ptah_id' => 2, 'ptih_id' => 1, 'ptph_id' => 0, 'name' => 'booking-payment-manual-refund-full', 'sys_name' => 'Booking Payment Manual Refund Full', 'title'=>'Refund', 'status' => '1');
//        $data[] = array('id' => 11, 'ptmh_id' => 1, 'ptch_id' => 2, 'ptah_id' => 2, 'ptih_id' => 2, 'ptph_id' => 0, 'name' => 'booking-payment-manual-refund-partial', 'sys_name' => 'Booking Payment Manual Refund Partial', 'title'=>'Refund', 'status' => '1');
//        $data[] = array('id' => 12, 'ptmh_id' => 2, 'ptch_id' => 1, 'ptah_id' => 1, 'ptih_id' => 1, 'ptph_id' => 0, 'name' => 'security-deposit-auto-collection-full', 'sys_name' => 'Security Deposit Auto Collection Full', 'title'=>'Security Deposit Authorization', 'status' => '1');
//        $data[] = array('id' => 13, 'ptmh_id' => 2, 'ptch_id' => 1, 'ptah_id' => 2, 'ptih_id' => 1, 'ptph_id' => 0, 'name' => 'security-deposit-auto-refund-full', 'sys_name' => 'Security Deposit Auto Refund Full', 'title'=>'Refund', 'status' => '1');
//        $data[] = array('id' => 14, 'ptmh_id' => 2, 'ptch_id' => 2, 'ptah_id' => 1, 'ptih_id' => 0, 'ptph_id' => 0, 'name' => 'security-deposit-manual-collection', 'sys_name' => 'Security Deposit Manual Collection', 'title'=> 'Charges', 'status' => '1');
//        $data[] = array('id' => 15, 'ptmh_id' => 2, 'ptch_id' => 2, 'ptah_id' => 2, 'ptih_id' => 1, 'ptph_id' => 0, 'name' => 'security-deposit-manual-refund-full', 'sys_name' => 'Security Deposit Manual Refund Full', 'title'=>'Refund', 'status' => '1');
//        $data[] = array('id' => 16, 'ptmh_id' => 2, 'ptch_id' => 2, 'ptah_id' => 2, 'ptih_id' => 2, 'ptph_id' => 0, 'name' => 'security-deposit-manual-refund-partial', 'sys_name' => 'Security Deposit Manual Refund Partial', 'title'=>'Refund', 'status' => '1');
//        $data[] = array('id' => 17, 'ptmh_id' => 3, 'ptch_id' => 1, 'ptah_id' => 2, 'ptih_id' => 0, 'ptph_id' => 0, 'name' => 'booking-payment-manual-additional-charge', 'sys_name' => 'Booking Payment Manual Additional Charge', 'title'=>'Extra Charges', 'status' => '1');
//        $data[] = array('id' => 18, 'ptmh_id' => 4, 'ptch_id' => 1, 'ptah_id' => 1, 'ptih_id' => 1, 'ptph_id' => 0, 'name' => 'booking-cancellation-auto-collection-full', 'sys_name' => 'Cancellation Fee Collection(Auto)', 'title'=>'Refund', 'status' => '1');
//
//        $data[] = array('id' => 19, 'ptmh_id' => 2, 'ptch_id' => 3, 'ptah_id' => 1, 'ptih_id' => 1, 'ptph_id' => 0, 'name' => 'security-deposit-auto-authorize', 'sys_name' => 'Security Deposit Auto Authorize', 'title'=> 'Security Deposit Authorization', 'status' => '1');
//        $data[] = array('id' => 20, 'ptmh_id' => 2, 'ptch_id' => 3, 'ptah_id' => 2, 'ptih_id' => 1, 'ptph_id' => 0, 'name' => 'security-deposit-manual-authorize', 'sys_name' => 'Security Deposit Manual Authorize', 'title'=> 'Security Deposit Authorization', 'status' => '1');
//        $data[] = array('id' => 21, 'ptmh_id' => 5, 'ptch_id' => 3, 'ptah_id' => 1, 'ptih_id' => 1, 'ptph_id' => 0, 'name' => 'credit-card-auto-authorize', 'sys_name' => 'Credit Card Auto Authorize', 'title'=> 'Authorization', 'status' => '1');
//        $data[] = array('id' => 22, 'ptmh_id' => 5, 'ptch_id' => 3, 'ptah_id' => 2, 'ptih_id' => 1, 'ptph_id' => 0, 'name' => 'credit-card-manual-authorize', 'sys_name' => 'Credit Card Manual Authorize', 'title'=> 'Authorization', 'status' => '1');
//        $data[] = array('id' => 23, 'ptmh_id' => 0, 'ptch_id' => 2, 'ptah_id' => 1, 'ptih_id' => 0, 'ptph_id' => 0, 'name' => 'Auto Refund', 'sys_name' => 'Auto Refund', 'title'=> 'Refund', 'status' => '1');
//
//        foreach ($data as $pivot)
//            PaymentTypePivotTable::create($pivot);
    }

}
