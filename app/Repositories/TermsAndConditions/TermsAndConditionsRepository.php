<?php


namespace App\Repositories\TermsAndConditions;



use App\PropertyInfo;
use App\TermsAndCondition;
use App\TermsAndConditionRentalBridge;
use Illuminate\Support\Facades\Auth;
use mysql_xdevapi\Exception;

class TermsAndConditionsRepository
{


    /** Create New
     * @param array $data
     * @return mixed
     */
    public function  create(array $data)
    {
        $newTerm=Auth::user()->user_account->termsAndConditions()->create([
            'user_id'=>Auth::user()->id,
            'internal_name'=>$data['internal_name'],
            'text_content'=>$data['text_content'],
            'checkbox_text'=>$data['checkbox_text'],
            'required'=>$data['required'],
            'status'=>$data['status'],
        ]);
        $this->attachProperties($data['selected_properties'],PropertyInfo::TAC,$newTerm->id);
       return $newTerm;
    }

    /**Update Old
     * @param array $data
     * @return bool
     */
    public  function  update(array $data)
    {

        TermsAndCondition::where('id',$data['serve_id'])->update([
            'internal_name'=>$data['internal_name'],
            'text_content'=>$data['text_content'],
            'checkbox_text'=>$data['checkbox_text'],
            'required'=>$data['required'],
            'status'=>$data['status'],
        ]);
        $this->attachProperties($data['selected_properties'],PropertyInfo::TAC,$data['serve_id'],true);
        return true;
    }
    public function updateStatus(int $id,array $data){
        TermsAndCondition::where('id',$id)->update($data);
        return true;
    }


    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        return true;
    }

    /** Get A Single Record
     * @param int $id
     * @return mixed
     */
    public function getOne(int $id)
    {
        return Auth::user()->user_account->termsAndConditions()->where('id',$id)->get();
    }

    /** All Records
     * @return mixed
     */
    public function getAll()
    {
        return Auth::user()->user_account->termsAndConditions;
    }



    /** Attach Properties With Record
     * @param array $selected_properties
     * @param string $model
     * @param string $foreign_value
     * @param bool $isUpdate
     */
    public function attachProperties(array $selected_properties, string  $model, string $foreign_value, bool $isUpdate=false){
        $foreign_key=PropertyInfo::BRIDGED_MODEL_META[$model]['column'];
        $properties=$this->formatAttachedData($selected_properties,$foreign_key,$foreign_value);
        $bridge=resolve(PropertyInfo::BRIDGED_MODELS[$model]);
        if($isUpdate){
            $bridge->where($foreign_key,$foreign_value)->delete();
        }
        $bridge->insert($properties);
    }

    /** Format Properties to Insert in Required Bridge Table
     * @param array $selected_properties
     * @param string $foreign_key
     * @param string $foreign_value
     * @return array
     */
    private function formatAttachedData(array $selected_properties, string $foreign_key, string $foreign_value){
        $properties=[];
        $now = now()->toDateTimeString();
        foreach ($selected_properties as $property){
            $rooms=(in_array(0,$property['attached_rooms'])?[]:$property['attached_rooms']);
            $properties[]=[
                $foreign_key=>$foreign_value,
                'property_info_id'=>$property['id'],
                'room_info_ids'=>room_infos_to_string($rooms),
                'created_at' => $now,
                'updated_at' => $now,

            ];
        }
        return $properties;
    }
}