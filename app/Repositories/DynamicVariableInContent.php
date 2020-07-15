<?php

namespace App\Repositories;

use App\BookingInfo;
use App\Repositories\DynamicVariableNamingRepo;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Support\Facades\Lang;

class DynamicVariableInContent
{

    private $removeExtraVar = true;
    private $all_model_variables=null;
    private $main_model_vars = null;
    private $main_model_relations= null;

    public function replaceWithActualData($model_name, $model_primary_key, $content_array, $removeExtraVar = true)
    {
        $this->all_model_variables = config('db_const.template_variables_naming');

        /** Check if Main Model Exists in Template Variables.
         *  If Not it will return data as it is.
         */
        if(empty($this->all_model_variables[$model_name])){
            return $content_array;
        }

        /** Replace Data   */
        $content_array = $this->replaceActualData($model_name, $model_primary_key, $content_array, $removeExtraVar);
        //dump($content_array);
        /** Check if Any Template Variables left in system */

        $hasExtraVars = preg_grep('~' . preg_quote('{', '~') . '~', array_flatten($content_array));

        if(count($hasExtraVars)
            && $model_name != BookingInfo::class
            && !empty($this->main_model_relations[BookingInfo::class])) {

            $biRelation  = $this->getRelationRecord(
                $model_name::find($model_primary_key),
                $this->main_model_relations[BookingInfo::class]
            );

            if (!empty($biRelation)) {
                $content_array = $this->replaceActualData(BookingInfo::class, $biRelation->id ,$content_array);
            }
        }

        //if model not available in our list simply return same content
        return $content_array;

    }

    private function replaceActualData($model_name, $model_primary_key, $content_array, $removeExtraVar = true){
        $this->removeExtraVar = $removeExtraVar;

        /** Get Main Model Variables  */
        $this->main_model_vars = $this->all_model_variables[$model_name]['variables'];
        /** Getting Main Model Relations */
        if(isset($this->all_model_variables[$model_name]['relationships'])){
            $this->main_model_relations = $this->all_model_variables[$model_name]['relationships'];
        }
        //get array of keys that exist in coming content
        $required_template_vars_only = $this->getOnlyRequiredKeysFromContent($content_array);
        $required_relations = $this->getRequiredRelationshipsName($required_template_vars_only);
        // now run only one query with all filtered relations
        // only pass support_eager_loading array for query, we will use lazy loading relations afterwards
        $master_query_result = $this->runSingleMasterQuery($model_name, $model_primary_key, $required_relations['support_eager_loading']);

        $replace_array = $this->makeSingleReplacementArrayFromQueryResult($master_query_result, $required_template_vars_only);

        $content_array =  $this->replaceContent($content_array, $replace_array);
        return $content_array;

    }
    /*
     * get only required key array from content
     */
    private function getOnlyRequiredKeysFromContent($content_array)
    {
        /**
         *  This Old Line Of Code Was Not Converting multi dimensional array to String  ->  $all_content_together = implode(' ', $content_array);
         */

        $all_content_together = $this->convertArrayToString("",$content_array);
        //call function to get single array with all the template variables even from relationship
        $all_available_keys_for_model = $this->singleArrayForAllAvailableTemplateVariablesForModel();

        //make an array only for the keys that are need to be replaced in the content
        return array_where($all_available_keys_for_model, function($value, $key) use($all_content_together)
        {
            return strpos($all_content_together, $value) == true;
        });
    }

    /**
     * This Function Will Recursively Convert Array To String.
     * @param $content
     * @param $data
     * @return string
     */
    private function convertArrayToString($content, $data){
        $implodeWith = " ";
        if(!is_array($data)){
            $content .= $implodeWith.$data;
        }else{
            foreach ($data as $key=>$value){
                $content = $this->convertArrayToString($content,$value);
            }
        }
        return $content;
    }

    /*
     * Replace all keys with final array
     */
    private function replaceContent($content_array,$replace_array)
    {
        //now replace if existed
        //dump([$content_array,$content_keys, $replace_array]);

        if($content_array && is_array($content_array))
        {

            foreach ($content_array as $key=>$content) {
                $content_array[$key] = $this->replaceData($replace_array, $content);
            }
        }

        return $content_array;
    }

    /**
     *  This Function Will Recursively Replace Template variables In Content Array With Actual Content.
     * @param $replace_array
     * @param $data
     * @return array|string|string[]
     */
    private function replaceData($replace_array, $data){

        if(!is_array($data)){
            foreach ($replace_array as $var => $value ){
                $data = str_replace($var,$value,$data);
            }
        }else{
            foreach ($data as $key => $value) {
                $data[$key] = $this->replaceData($replace_array,$value);
            }
        }
        return $data;
    }

    /*
     * run single DB query with all relationships
     */
    private function runSingleMasterQuery($model_name, $model_primary_key, $required_relations)
    {
        return resolve($model_name)::with(array_keys($required_relations))
                                    ->where('id', $model_primary_key)->first();

    }

    /*
     * single default array which have all available template variables for requested model
     * This also includes all relationships template to make final single array
     */
    private function singleArrayForAllAvailableTemplateVariablesForModel()
    {
        $single_arr = [];
        
        foreach ($this->main_model_vars as $key=>$value) {
            $single_arr[] = $value;
        }

        //also get template variables name from all relations
        if (!empty($this->main_model_relations)) {

            foreach ($this->main_model_relations as $key => $value) {
                
                foreach ($this->all_model_variables[$key]['variables'] as $k => $v) {
                    $single_arr[] = $v;
                }
            }
        }

        return $single_arr;
    }

    /*
     * Find out all required relationship names for requested content
     */
    private function getRequiredRelationshipsName($required_template_vars_only)
    {
        $required_relations['support_eager_loading'] = [];
        $required_relations['not_support_eager_loading'] = [];

        foreach ($this->main_model_vars as $key=>$value)
        {
            if(in_array($value, $required_template_vars_only))
            {
                if (($match_key = array_search($value, $required_template_vars_only)) !== false) {
                    unset($required_template_vars_only[$match_key]);
                }
            }
        }

        /*
         * now check if any other template variable pending 
         * if yes then means that is related to some other model
         * so loop through all relationship for request model
        */
        if(count($required_template_vars_only)>0)
        {
            foreach ($this->main_model_relations as $relation_model => $relation_definition)
            {
                
                //now check if required keys match with each model's column 
                if(isset($this->all_model_variables[$relation_model]['variables']) && count($required_template_vars_only)>0)
                {
                    foreach ($this->all_model_variables[$relation_model]['variables'] as $column_name => $template_name) {
                        if(in_array($template_name, $required_template_vars_only))
                        {
                            if (($match_key = array_search($template_name, $required_template_vars_only)) !== false)
                            {
                                unset($required_template_vars_only[$match_key]);
                            }

                            //use this array later on for select columns with query
                            //check if current looping relation supports eager loading or not
                            if(isset($relation_definition['support_eager_loading']) && $relation_definition['support_eager_loading'] == false)
                                $required_relations['not_support_eager_loading'][$relation_definition['relationship_name']][] = $column_name;
                            else
                                $required_relations['support_eager_loading'][$relation_definition['relationship_name']][] = $column_name;
                        }
                    }
                }
            }
        }

        //array of arrays means we are sending eager loading supporting and non-supporting in different array keys
        return $required_relations;
    }

    /*
     * Now we have query result which can have relation data 
     * Make single one-dimensional data to use for replacement
     */
    private function makeSingleReplacementArrayFromQueryResult($master_query_result, $required_template_vars_only)
    {
        $replace_array = [];

        if($master_query_result)
        {
          //  dump($this->main_model_vars);
            foreach ($this->main_model_vars as $key=>$value)
            {
                if(in_array($value, $required_template_vars_only))
                {
                    if (($match_key = array_search($value, $required_template_vars_only)) !== false) {
                        unset($required_template_vars_only[$match_key]);
                    }

                    //dump($master_query_result->{$key});
                    //push to final array to replace values at last
                    if(isset($master_query_result->{$key}) && $master_query_result->{$key} !== null) {
                        $replace_array[$value] = $this->formatVariablesData($master_query_result,$key,$value);
                    } else {
                        $replace_array[$value] = 'N/A';
                    }
                }
            }
            /*
             * now check if any other template variable pending 
             * if yes then means that is related to some other model
             * so loop through all relationship for request model
            */
            if(count($required_template_vars_only)>0)
            {
                foreach ($this->main_model_relations as $key => $value)
                {

                    //now check if required keys match with each model's column
                    if(isset($this->all_model_variables[$key]['variables']) && count($required_template_vars_only)>0) {

                        foreach ($this->all_model_variables[$key]['variables'] as $k => $v) {

                            if(in_array($v, $required_template_vars_only)) {

                                if (($match_key = array_search($v, $required_template_vars_only)) !== false) {
                                    unset($required_template_vars_only[$match_key]);
                                }

                                //push to final array to replace values at last
                                $needed_relation_record = $this->getRelationRecord($master_query_result, $value);;

                                if (!empty($needed_relation_record)) {

                                    if ($value['results'] === 'plural') {

                                        if (isset($value['first_or_last']) && $value['first_or_last'] == 'first') {
                                            $needed_relation_record = $needed_relation_record->first();
                                        } else {
                                            $needed_relation_record = $needed_relation_record->last();
                                        }
                                    }
                                }


                                 if (isset($replace_array[$v])) {
                                    // Already Set dn't replace with N/A.
                                } elseif(isset($needed_relation_record->{$k}) && $needed_relation_record->{$k} !== null) {
                                    //push to final replacing array to replace the this key in content
                                    $replace_array[$v] = $this->formatVariablesData($needed_relation_record,$k,$v);
                                } else {
                                    $replace_array[$v] = 'N/A';
                                }
                            }
                        }
                    }
                }
            }
        }

        return $replace_array;
    }


    /**
     *  Following Function Will Format the Template Variable Data As Per Requirement.
     * i.e Data Time Format, Transaction_Type from Language File, Booking status From Config File
     * @param $collection
     * @param $key
     * @param $variable
     * @return string
     */

    private function formatVariablesData($collection, $key, $variable) {

        $data = $collection->{$key};

        switch ($variable) {
            case'{Checkin_Date}':
            case '{Checkout_Date}':
                $data = Carbon::parse($data)->timezone($collection->property_info->time_zone)->format('M d, Y');
            break;

            case '{Booking_Time}':
                $data = Carbon::parse($data)->timezone($collection->property_info->time_zone)->format('M d, Y H:i a');
            break;

            case '{Transaction_Due_Date}':
            case '{Transaction_Next_Attempt_Date}':
            case '{Authorization_Due_Date}':
            case '{Authorization_Next_Attempt_Date}':
                $data = $this->propertyTime($collection, $data, 'M d Y h:i:s a');
                break;

            case '{Transaction_Type}':
            case '{Authorization_Type}':
                $data = Lang::get('client/transaction_types.transaction_type.'.$data.'.sys_name');
                break;

            case '{PMS_Booking_Status}':
                $data = array_search ($data, config('db_const.booking_info.pms_booking_status'));
                break;

            default:
                //
                break;
        }

        return $data;
    }

    /**
     * @param null $instance
     * @param null $datetime
     * @param string $format
     * @return string|null
     */
    public  function propertyTime($instance = null, $datetime = null, $format = 'M d, Y') {

        //dump($instance->{$this->main_model_relations[BookingInfo::class]['relationship_name']});
        // Property TimeZone from BookingInfo to PropertyInfo relation
        if (!empty($instance)
            && !empty($datetime)
            && !empty($this->main_model_relations[BookingInfo::class])
            && !empty($instance->{$this->main_model_relations[BookingInfo::class]['relationship_name']}->property_info->time_zone)) {

            // Format GMT DateTime to Property Time Zone
            $datetime = Carbon::parse($datetime)->timezone(
                $instance->{$this->main_model_relations[BookingInfo::class]['relationship_name']}->property_info->time_zone
            )->format($format);
        }

        return $datetime;
    }


    /**
     * @param $master_query_result
     * @param $relation_array
     * @return mixed | null
     */
    private function getRelationRecord($master_query_result, $relation_array) {
        $relations = explode('.', $relation_array['relationship_name']);
        $instance = $master_query_result;
        foreach ($relations as $relation) {
            if (!empty($instance->{$relation})) {
                $instance = $instance->{$relation};
            } else {
                return null;
            }
        }

        return $instance;
    }
}
