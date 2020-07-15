<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class PaymentTypePivotTable extends Model implements Auditable
{
	use AuditableTrait;
	
    public function payment_type_main_head()
		{

    	
    		return $this->belongsTo('App\PaymentTypeMainHead');
		}

	 public function payment_type_collection_head()
		{

    	
    		return $this->belongsTo('App\PaymentTypeCollectionHead');
		}

	 public function payment_type_automation_head()
		{

    	
    		return $this->belongsTo('App\PaymentTypeAutomationHead');
		}


	 public function payment_type_installment_head()
		{

    	
    		return $this->belongsTo('App\PaymentTypeInstallmentHead');
		}


	 public function payment_type_partial_head()
		{

    	
    		return $this->belongsTo('App\PaymentTypePartialHead');
		}


}
