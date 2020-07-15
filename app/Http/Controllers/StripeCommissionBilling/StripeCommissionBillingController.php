<?php


namespace App\Http\Controllers\StripeCommissionBilling;


use App\Http\Controllers\Controller;
use App\System\StripeCommissionBilling\StripeCommissionBilling;
use App\System\StripeCommissionBilling\StripeCommissionBillingInterface;
use Illuminate\Http\Request;

class StripeCommissionBillingController extends Controller
{
    private $systemCommissionBilling;

    public function __construct(StripeCommissionBillingInterface $stripeCommissionBilling)
    {
        $this->systemCommissionBilling = $stripeCommissionBilling;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function stripeWebHook(Request $request)
    {
        /**
         * @var StripeCommissionBilling
         */
        return $this->systemCommissionBilling->ListenStripeEvent($request);
    }
}
