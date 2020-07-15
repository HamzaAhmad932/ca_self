<?php


namespace App\Repositories\Upsells;


use App\Http\Requests\UpsellStoreRequest;
use App\PropertyInfo;
use App\UpsellListing;
use App\UpsellPropertiesBridge;
use App\UpsellType;

class UpsellListingRepository implements UpsellListingRepositoryInterface
{

    /***
     * @param int $user_account_id
     * @return mixed
     */
    public function getUpsellTypes(int $user_account_id = 0)
    {
        return UpsellType::where('is_user_defined',
            config('db_const.upsell_type.is_user_defined.system_defined.value'))
            ->orWhere('user_account_id', $user_account_id)
            ->get(['id', 'title', 'is_user_defined']);
    }

    /**
     * @param UpsellStoreRequest $request
     * @param $upsell_id
     * @return mixed
     */
    public function storeUpsellListing(UpsellStoreRequest $request)
    {

        $upsell = UpsellListing::updateOrCreate(
            [
                'user_account_id' => auth()->user()->user_account_id,
                'id' => $request->upsell_id,
            ],
            [
                'user_account_id' => auth()->user()->user_account_id,
                'user_id' => auth()->user()->id,
                'upsell_type_id' => $request->upsell_type_id,
                'internal_name' => $request->internal_name,
                'meta' => $request->__meta,
                'value_type' => config('db_const.upsell_listing.value_type.flat.value'), // Default Flat...
                'value' => $request->value,
                'per' => $request->per,
                'period' => $request->period,
                'notify_guest' => abs($request->notify_guest),
                'status' => $request->status,
            ]
        );

        $this->storeUpsellListingBridgeEntry($request, $upsell); // Bridge Entry
        $upsell->load('upsellPropertiesBridge');

        return $upsell;
    }

    /**
     * @param UpsellStoreRequest $request
     * @param UpsellListing $upsell
     */
    public function storeUpsellListingBridgeEntry(UpsellStoreRequest $request, UpsellListing $upsell)
    {
        $records = self::selectedProperties($request, $upsell->id);

        UpsellPropertiesBridge::where('upsell_listing_id', $upsell->id)->delete();

        if (!empty($records))
            UpsellPropertiesBridge::insert($records);
    }



    /**
     * @param int $user_account_id
     * @param string $model_key
     * @param int $serve_id
     * @return mixed
     */
    public function  bridgeAllPropertiesWithRooms(int $user_account_id, string $model_key, int $serve_id = 0)
    {
        $bridge_relation = PropertyInfo::BRIDGED_MODEL_META[$model_key]['relation'];
        $properties = PropertyInfo::where([
            ['user_account_id', $user_account_id],
            ['available_on_pms', 1]
        ])->with(['room_info' => function ($query) {
            $query->where('available_on_pms', 1)
                ->select('id','property_info_id', 'name', 'pms_room_id');
        }])->with([
                $bridge_relation => function ($query) use ($model_key, $serve_id)
                {
                    if($model_key != PropertyInfo::TAC){
                        $query->where(PropertyInfo::BRIDGED_MODEL_META[$model_key]['column'], $serve_id);
                    }
                }
            ])->select('id', 'name', 'pms_property_id', 'user_account_id')->get();
        $properties = $this->mapAttribute($properties, [
            'bridge_relation'=>$bridge_relation,
            'bridge_column'=>PropertyInfo::BRIDGED_MODEL_META[$model_key]['column'],
            'serve_id'=>$serve_id
        ]);

        /** Properties Filter For Terms and Conditions */
        if($model_key == PropertyInfo::TAC){
            $data = $properties;
            $properties =  $data->filter(
                function ($item) use ($bridge_relation,$serve_id){
                    /**  Select Bridge Entries */
                    $bridge_items=$item->{$bridge_relation};
                    /**  Check if Bridge Table Has Entries */
                    if(count($bridge_items)!==0){
                       /**
                        * Check if All rentals have Terms and Conditions Attached Or Some Rentals
                        * Null in Room_info_ids means All Rental Selected
                       */
                        if(!is_null($bridge_items[0]->room_info_ids)){
                            foreach ($bridge_items as $key=>$bridge_item){
                               /** Check if this is called For Edit. If it is it will skip that entry */
                                if($bridge_item->terms_and_condition_id != $serve_id){
                                    $bridge_item->status=false;
                                    $used = $bridge_item->room_info_ids;
                                    /** Filtering the available rooms form the room infos*/
                                    $item->room_info=$item->room_info->filter(function ($room)use($used){
                                        return !in_array($room->id,$used);
                                    });
                                }
                            }
                           /** If All Rentals of that property has terms and conditions attached
                            * then it will pop out from The Collection
                            */
                            if(count($item->room_info) != 0){
                                return $item;
                            }
                        }else{
                           /**
                            * if This is called for Edit then it will not pop the property from collection Otherwise It Will.
                            */
                            if($bridge_items[0]->terms_and_condition_id == $serve_id){
                                return $item;
                            }
                        }
                    }else{
                       /**
                        * Return the Property without extra filtration if This Property has no bridge table Entry
                        */
                        return $item;
                    }

                });
        }

        /** END Filter */
        return $properties;
    }


    /**
     * @param $collection
     * @param $name
     * @param $attribute
     * @return mixed
     */
    private function mapAttribute($collection, $attributes)
    {
        return $collection->map(function($instance) use ($attributes) {
            foreach ($attributes as $name => $attribute){
                $instance[$name] = $attribute;
            }
            return $instance;
        });
    }

    /**
     * @param $request
     * @param $module_foreign_id
     * @return array
     */
    public static function selectedProperties($request, $module_foreign_id)
    {
        if (empty($request->selected_properties))
            return [];

        $property_infos = auth()->user()->user_account->properties_info->pluck('id')->toArray();
        $now = now()->toDateTimeString();
        $records = array();

        foreach ($request->selected_properties as $property) {
            if (!empty($property['attach_status']) && !empty($property['id'])  && in_array($property['id'], $property_infos)) {

                $room_infos_ids = in_array(0, $property['attached_rooms'])
                    ? [] : $property['attached_rooms']; //0 => All Rentals

                array_push($records,
                    [
                        'upsell_listing_id' => $module_foreign_id,
                        'property_info_id' => $property['id'],
                        'room_info_ids' => room_infos_to_string($room_infos_ids),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
        return $records;
    }

    /**
     * @param int $user_account_id
     * @param int $upsell_id
     * @return mixed
     */
    public function getUserUpsellListing(int $user_account_id, int $upsell_id)
    {
       return UpsellListing::where([['user_account_id', $user_account_id], ['id', $upsell_id]])->first();
    }

    /**
     * @param int $upsell_id
     * @param bool $status
     * @return mixed
     */
    public function changeUpsellListingStatus(int $upsell_id, bool $status)
    {
        return UpsellListing::where(
            [
                ['id', $upsell_id],
                ['user_account_id', auth()->user()->user_account_id]
            ]
        )->update(['status' => $status]);
    }
}