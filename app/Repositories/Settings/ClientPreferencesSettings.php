<?php 
/**
 * Created by PhpStorm.
 * User: Suleman Afzal
 * Date: 21-March-19
 * Time: 4:47 PM
 */

namespace App\Repositories\Settings;

use App\PreferencesForm;
use App\UserPreference;
use Illuminate\Support\Facades\Log;


class ClientPreferencesSettings 
{
	private $userAccountId;
	private $preferencesDefault;
  private $userPreference;
	
	function __construct($userAccountId){
	  
	  $this->userAccountId = $userAccountId;
		$this->preferencesDefault = PreferencesForm::get();
		$this->userPreference = UserPreference::where('user_account_id' , $this->userAccountId)->get();
	}


 /**
 *  User_preference Config file var 
 * @param $configVar
 * @return Object | false
 */


  public function getPreferences( $configVar ){
     
    if($this->userPreference->count() > 0){ 

      $setting = $this->userPreference->where('preferences_form_id',  $configVar )->last();

     if(is_null($setting))
        return $this->defaultPreference($configVar);
    
      return json_decode($setting->form_data);
    }
    return $this->defaultPreference($configVar);
  }



 /**
 *  User_preference Config file var 
 * @param $configVar
 *
 * @return Object | false
 */


	private function defaultPreference( $configVar ){
        
    $defaultPreference = $this->preferencesDefault->where('form_id', $configVar)->first();

    if (!is_null($defaultPreference))
      return json_decode($defaultPreference->form_data);	
      
    return false;
  }

}