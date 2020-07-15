<?php
/**
 * Created by PhpStorm.
 * User: GM
 * Date: 28-Dec-18
 * Time: 2:54 PM
 */

namespace App\Repositories\Settings;

use App\PaymentTypeAutomationHead;
use App\PaymentTypeCollectionHead;
use App\PaymentTypeInstallmentHead;
use App\PaymentTypeMainHead;
use App\PaymentTypePartialHead;
use App\PaymentTypePivotTable;

class PaymentTypeMeta
{

    private $collection;
    private $main_heads;
    private $automation_heads;
    private $collection_heads;
    private $installment_heads;
    private $partial_heads;

    public function __construct()

    {
        $this->collection = PaymentTypePivotTable::all();
        $this->main_heads = PaymentTypeMainHead::all();
        $this->automation_heads = PaymentTypeAutomationHead::all();
        $this->collection_heads = PaymentTypeCollectionHead::all();
        $this->installment_heads = PaymentTypeInstallmentHead::all();
        $this->partial_heads = PaymentTypePartialHead::all();


    }
    public function getMainHeads($name)
    {
        return $this->main_heads->where('name', $name)->first()->id;
    }
    public function getAutomationHeads($name)
    {
        return $this->automation_heads->where('name', $name)->first()->id;
    }
    public function getCollectioHeads($name)
    {
        return $this->collection_heads->where('name', $name)->first()->id;
    }
    public function getInstallmentHeads($name)
    {
        return $this->installment_heads->where('name', $name)->first()->id;
    }
    public function getPartialHeads($name)
    {
        return $this->partial_heads->where('name', $name)->first()->id;
    }


    public function getBookingPaymentAutoCollectionFull()
    {

        $data = $this->collection
            ->where('ptmh_id', 1)
            ->where('ptah_id', 1)
            ->where('ptch_id', 1)
            ->where('ptih_id', 1)
            ->where('ptph_id', 0)->first();


        return $data->id;
    }

    public function getBookingPaymentAutoCollectionPartial1of2()
    {

        $data = $this->collection
            ->where('ptmh_id', 1)
            ->where('ptah_id', 1)
            ->where('ptch_id', 1)
            ->where('ptih_id', 2)
            ->where('ptph_id', 1)->first();


        return $data->id;
    }

    public function getBookingPaymentAutoCollectionPartial2of2()
    {

        $data = $this->collection
            ->where('ptmh_id', 1)
            ->where('ptah_id', 1)
            ->where('ptch_id', 1)
            ->where('ptih_id', 2)
            ->where('ptph_id', 2)->first();


        return $data->id;
    }

    public function getBookingPaymentAutoRefundFull()
    {

        $data = $this->collection
            ->where('ptmh_id', 1)
            ->where('ptah_id', 1)
            ->where('ptch_id', 2)
            ->where('ptih_id', 1)
            ->where('ptph_id', 0)->first();


        return $data->id;
    }

    public function getBookingPaymentAutoRefundPartial()
    {

        $data = $this->collection
            ->where('ptmh_id', 1)
            ->where('ptah_id', 1)
            ->where('ptch_id', 2)
            ->where('ptih_id', 2)
            ->where('ptph_id', 0)->first();


        return $data->id;
    }

    public function getBookingPaymentManualCollectionFull()
    {

        $data = $this->collection
            ->where('ptmh_id', 1)
            ->where('ptah_id', 2)
            ->where('ptch_id', 1)
            ->where('ptih_id', 1)
            ->where('ptph_id', 0)->first();


        return $data->id;
    }

    public function getBookingPaymentManualCollectionPartial()
    {

        $data = $this->collection
            ->where('ptmh_id', 1)
            ->where('ptah_id', 2)
            ->where('ptch_id', 1)
            ->where('ptih_id', 2)
            ->where('ptph_id', 0)->first();


        return $data->id;
    }

    public function getBookingPaymentManualCollectionPartial1of2()
    {

        $data = $this->collection
            ->where('ptmh_id', 1)
            ->where('ptch_id', 2)
            ->where('ptah_id', 1)
            ->where('ptih_id', 2)
            ->where('ptph_id', 1)->first();


        return $data->id;
    }

    public function getBookingPaymentManualCollectionPartial2of2()
    {

        $data = $this->collection
            ->where('ptmh_id', 1)
            ->where('ptch_id', 2)
            ->where('ptah_id', 1)
            ->where('ptih_id', 2)
            ->where('ptph_id', 2)->first();


        return $data->id;
    }

    public function getBookingPaymentManualRefundFull()
    {

        $data = $this->collection
            ->where('ptmh_id', 1)
            ->where('ptah_id', 2)
            ->where('ptch_id', 2)
            ->where('ptih_id', 1)
            ->where('ptph_id', 0)->first();


        return $data->id;
    }

    public function getBookingPaymentManualRefundPartial()
    {

        $data = $this->collection
            ->where('ptmh_id', 1)
            ->where('ptah_id', 2)
            ->where('ptch_id', 2)
            ->where('ptih_id', 2)
            ->where('ptph_id', 0)->first();


        return $data->id;
    }

    public function getSecurityDepositAutoCollectionFull()
    {

        $data = $this->collection
            ->where('ptmh_id', 2)
            ->where('ptah_id', 1)
            ->where('ptch_id', 1)
            ->where('ptih_id', 1)
            ->where('ptph_id', 0)->first();


        return $data->id;
    }

    public function getSecurityDepositAutoRefundFull()
    {

        $data = $this->collection
            ->where('ptmh_id', 2)
            ->where('ptah_id', 1)
            ->where('ptch_id', 2)
            ->where('ptih_id', 1)
            ->where('ptph_id', 0)->first();


        return $data->id;
    }

    public function getSecurityDepositManualCollection()
    {

        $data = $this->collection
            ->where('ptmh_id', 2)
            ->where('ptah_id', 1)
            ->where('ptch_id', 2)
            ->where('ptih_id', 0)
            ->where('ptph_id', 0)->first();

        return $data->id;
    }

    public function getSecurityDepositManualRefundFull()
    {

        $data = $this->collection
            ->where('ptmh_id', 2)
            ->where('ptah_id', 2)
            ->where('ptch_id', 2)
            ->where('ptih_id', 1)
            ->where('ptph_id', 0)->first();


        return $data->id;
    }

    public function getSecurityDepositManualRefundPartial()
    {

        $data = $this->collection
            ->where('ptmh_id', 2)
            ->where('ptah_id', 2)
            ->where('ptch_id', 2)
            ->where('ptih_id', 2)
            ->where('ptph_id', 0)->first();


        return $data->id;
    }
    public function getBookingPaymentManualAdditionalCharge()
    {

        $data = $this->collection
            ->where('ptmh_id', 3)
            ->where('ptah_id', 2)
            ->where('ptch_id', 1)
            ->where('ptih_id', 0)
            ->where('ptph_id', 0)->first();


        return $data->id;
    }

    public function getBookingCancellationAutoCollectionFull()
    {

        $data = $this->collection
            ->where('ptmh_id', 4)
            ->where('ptah_id', 1)
            ->where('ptch_id', 1)
            ->where('ptih_id', 1)
            ->where('ptph_id', 0)->first();


        return $data->id;
    }

    public function getSecurityDepositAutoAuthorize()
    {

        $data = $this->collection
            ->where('ptmh_id', 2)
            ->where('ptah_id', 1)
            ->where('ptch_id', 3)
            ->where('ptih_id', 1)
            ->where('ptph_id', 0)->first();


        return $data->id;
    }

    public function getSecurityDepositManualAuthorize()
    {

        $data = $this->collection
            ->where('ptmh_id', 2)
            ->where('ptah_id', 2)
            ->where('ptch_id', 3)
            ->where('ptih_id', 1)
            ->where('ptph_id', 0)->first();


        return $data->id;
    }

    public function getCreditCardAutoAuthorize()
    {

        $data = $this->collection
            ->where('ptmh_id', 5)
            ->where('ptah_id', 1)
            ->where('ptch_id', 3)
            ->where('ptih_id', 1)
            ->where('ptph_id', 0)->first();


        return $data->id;
    }

    public function getCreditCardManualAuthorize()
    {

        $data = $this->collection
            ->where('ptmh_id', 5)
            ->where('ptah_id', 2)
            ->where('ptch_id', 3)
            ->where('ptih_id', 1)
            ->where('ptph_id', 0)->first();


        return $data->id;
    }

    /**
     * use this function or transaction type only for Booking Payment Cancellation Refund Entries
     *
     * @return mixed
     */
    public function getAutoRefund()
    {

        $data = $this->collection
            ->where('ptmh_id', 0)
            ->where('ptah_id', 1)
            ->where('ptch_id', 2)
            ->where('ptih_id', 0)
            ->where('ptph_id', 0)->first();


        return $data->id;
    }

    public function getTransactionType($id)
    {
        $data = $this->collection
            ->where('id', $id)->first();
        if(!is_null($data))
            return $data->name;
        else
            return 'Transaction Type Not Found';
    }

    public function getTransactionTypeNameForUser($id)
    {
        $data = $this->collection
            ->where('id', $id)->first();

        if(!is_null($data)){
            $transactionTypeNameForUser = config('db_const.transaction_type_name_for_user.'.$data->sys_name);
            return ( $transactionTypeNameForUser != null ? $transactionTypeNameForUser : $data->sys_name);
        } else {
            return 'Transaction Type not define';
        }
    }

    public function getAuthTypeCCValidation() {

        $data = $this->collection
            ->where('ptmh_id', 5)
            ->where('ptah_id', 1)
            ->where('ptch_id', 3)
            ->where('ptih_id', 1)
            ->where('ptph_id', 0)->first();

        return $data->id;
    }

    public function getAuthTypeSecurityDamageValidation() {

        $data = $this->collection
            ->where('ptmh_id', 2)
            ->where('ptah_id', 1)
            ->where('ptch_id', 3)
            ->where('ptih_id', 1)
            ->where('ptph_id', 0)->first();

        return $data->id;
    }

    /**
     * @return array
     */
    public function getAllAdjustmentTypes() {
        $types = [];
        $types[] = $this->getSecurityDepositAutoCollectionFull();
        $types[] = $this->getSecurityDepositAutoRefundFull();
        $types[] = $this->getSecurityDepositManualCollection();
        $types[] = $this->getSecurityDepositManualRefundFull();
        $types[] = $this->getSecurityDepositManualRefundPartial();
        $types[] = $this->getSecurityDepositAutoAuthorize();
        $types[] = $this->getSecurityDepositManualAuthorize();
        $types[] = $this->getAuthTypeSecurityDamageValidation();
        $types[] = $this->getBookingCancellationAutoCollectionFull();
        return $types;
    }

    /**
     * @return array
     */
    public function getAllChargeTypes() {
        $types = [];
        $types[] = $this->getBookingPaymentAutoCollectionFull();
        $types[] = $this->getBookingPaymentAutoCollectionPartial1of2();
        $types[] = $this->getBookingPaymentAutoCollectionPartial2of2();
        $types[] = $this->getBookingPaymentManualCollectionFull();
        $types[] = $this->getBookingPaymentManualCollectionPartial();
        $types[] = $this->getBookingPaymentManualCollectionPartial1of2();
        $types[] = $this->getBookingPaymentManualCollectionPartial2of2();
        return $types;
    }

}