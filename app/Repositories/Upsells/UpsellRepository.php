<?php


namespace App\Repositories\Upsells;


use App\TermsAndConditionRentalBridge;
use Carbon\Carbon;
use App\BookingInfo;
use App\UpsellOrder;
use App\UpsellOrderDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Traits\Resources\General\Booking;
use App\Http\Requests\UpsellStoreRequest;
use App\PropertyInfo;
use App\UpsellCart;
use App\Upsell;
use App\UpsellPropertiesBridge;
use App\UpsellType;

class UpsellRepository implements UpsellRepositoryInterface
{
    use Booking;

    /***
     * @param int $user_account_id
     * @return mixed
     */
    public function getUpsellTypes(int $user_account_id = 0,$get_active_only=true,$serve_id=0)
    {
        /** Following Condition Will Check Whether active status check required or not.
         * If Request is for filters it will get all types either active or inactive.
         * OR if request is for add upsell form  it will only active upsell types.
         */
        if($get_active_only){
            $types = UpsellType::where('status', config('db_const.upsell_type.status.active.value'))->where('user_account_id', $user_account_id)->orWhere('is_user_defined',config('db_const.upsell_type.is_user_defined.system_defined.value'));
        }else{
            $types = UpsellType::where('user_account_id', $user_account_id)->orWhere('is_user_defined',config('db_const.upsell_type.is_user_defined.system_defined.value'));
        }

        /** If Request is for Edit Upsell it will surly return upsell type either type is active or not. */
        if($serve_id != 0 ){
            $upsell = Upsell::select('upsell_type_id')->where('id',$serve_id)->first();
            if(!empty($upsell)){
                $types->orWhere('id','=', $upsell->upsell_type_id);
            }
        }
//        dump($types->toSql());
        return $types->get(['id', 'title', 'is_user_defined']);;
    }

    /**
     * @param UpsellStoreRequest $request
     * @param $upsell_id
     * @return mixed
     */
    public function storeUpsells(UpsellStoreRequest $request)
    {

        $upsell = Upsell::updateOrCreate(
            [
                'user_account_id' => auth()->user()->user_account_id,
                'id' => $request->upsell_id,
            ],
            [
                'user_account_id' => auth()->user()->user_account_id,
                'user_id' => auth()->user()->id,
                'upsell_type_id' => $request->upsell_type_id,
                'internal_name' => $request->internal_name,
                'meta' => $request->meta_,
                'value_type' => config('db_const.upsell_listing.value_type.flat.value'), // Default Flat...
                'value' => $request->value,
                'per' => $request->per,
                'period' => $request->period,
                'notify_guest' => abs($request->notify_guest),
                'status' => $request->status,
            ]
        );

        $this->storeUpsellBridgeEntry($request, $upsell); // Bridge Entry
        $upsell->load('upsellPropertiesBridge');

        return $upsell;
    }

    /**
     * @param UpsellStoreRequest $request
     * @param Upsell $upsell
     */
    public function storeUpsellBridgeEntry(UpsellStoreRequest $request, Upsell $upsell)
    {
        $records = self::selectedProperties($request, $upsell->id);

        UpsellPropertiesBridge::where('upsell_id', $upsell->id)->delete();

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
            ['available_on_pms', 1],
            ['status', 1]
        ])
//            ->with([
//            'room_info' => function ($query) { $query->where('available_on_pms', 1)
//                ->select('id','property_info_id', 'name', 'pms_room_id');}])
            ->with([
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
            'serve_id'=>$serve_id,
            'allRentalsAvailable'=>true,
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
                            /**
                             * @var $bridge_item TermsAndConditionRentalBridge
                             */
                            foreach ($bridge_items as $key=>$bridge_item){
                                /** Check if this is called For Edit. If it is it will skip that entry */
                                if($bridge_item->terms_and_condition_id != $serve_id){
                                    $bridge_item->status=false;
                                    $used = $bridge_item->room_info_ids;
                                    /** Filtering the available rooms form the room infos*/
                                        $item->room_info=$item->room_info->filter(function ($room)use($used){
                                            return !in_array($room->id,$used);
                                        });
                                    $item->{$bridge_relation}->pull($key);
                                    $item->allRentalsAvailable = false;
                                }
                            }
                            /** If All Rentals of that property has terms and conditions attached
                             *  then it will pop out from The Collection
                             */
                            if(count($item->room_info) != 0){
                                return $item;
                            }
                        }else{
                            /**
                             * If This is called for Edit then it will not pop the property from collection Otherwise It Will.
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
     * @param $attributes
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
                        'upsell_id' => $module_foreign_id,
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
    public function getUserUpsells(int $user_account_id, int $upsell_id)
    {
       return Upsell::where([['user_account_id', $user_account_id], ['id', $upsell_id]])->first();
    }

    /**
     * @param int $upsell_id
     * @param bool $status
     * @return mixed
     */
    public function changeUpsellStatus(int $upsell_id, bool $status)
    {
        return Upsell::where(
            [
                ['id', $upsell_id],
                ['user_account_id', auth()->user()->user_account_id]
            ]
        )->update(['status' => $status]);
    }

    public function upsellListing(int $booking_info_id){

        try {
            $booking = BookingInfo::find($booking_info_id);

            $upsell_orders = $booking->upsellOrders->where('status', UpsellOrder::STATUS_PAID);
            $orders_ids = $upsell_orders->pluck('id');

            $ordered_upsell_ids = [];

            foreach($upsell_orders as $order) {
                $ordered_upsell_ids = array_merge($ordered_upsell_ids, $order->upsellOrderDetails->pluck('upsell_id')->toArray());
            }
            $room_info = $booking->room_info;
            $available_upsells = !empty($room_info) ? $room_info->upsells()->whereNotIn('id', $ordered_upsell_ids)->where('status', Upsell::STATUS_ACTIVE) : collect();

            /** Sort Upsells By Upsell Type Priority Oder  */
            $available_upsells = $available_upsells->sortByDesc(function ($upsell) {
                return $upsell->upsellType->priority;
            });

            // Already added upsells in cart
            $in_cart_upsells = $booking->upsellCarts->pluck('upsell_id', 'upsell_id')->toArray();

            // Map Already added upsells in cart Status to available upsells
            $available_upsells->map(function($instance) use ($in_cart_upsells) {
                $instance->in_cart = in_array($instance->id, $in_cart_upsells);
                return $instance;
            });
            // Already Purchased Upsells.
            $purchased_upsells = Upsell::with(['upsellType','upsellOrderDetails' => function($query) use ($orders_ids) {
                $query->whereIn('upsell_order_id', $orders_ids);
            }])->whereIn('id', $ordered_upsell_ids)->get();

            return collect(['available'=> $this->getUpsellResponse($booking_info_id, $available_upsells), 'purchased'=> $purchased_upsells]);
        } catch (\Exception $e) {
            log_exception_by_exception_object($e);
            return [];
        }
    }

    public function saveAddonCart(array $data){

        try {

            UpsellCart::where('booking_info_id', $data['booking_info_id'])->delete();

            $cart_content = [];
            foreach($data['upsell_listing_ids'] as $cart){

                $item['booking_info_id'] = $data['booking_info_id'];
                $item['upsell_id'] = $cart['id'];
                $item['persons'] = $cart['show_guest_count'] ? $cart['persons'] : 0;
                $item['created_at'] = now()->toDateTimeString();
                $item['updated_at'] = now()->toDateTimeString();
                array_push($cart_content, $item);
            }

            if(!empty($cart_content)){
                UpsellCart::insert($cart_content);
            }

            return true;
        }catch (\Exception $e){

            log_exception_by_exception_object($e);
            return null;
        }
    }

    public function removeBookingCart(int $booking_id){

        return UpsellCart::where('booking_info_id', $booking_id)->delete();
    }

    public function getUpsellByUpsellID(int $booking_id, array $upsell_ids){

        try {
            $upsells = Upsell::whereIn('id', $upsell_ids)->with(['upsellType'])->where('status', Upsell::STATUS_ACTIVE)->get();

            return $this->getUpsellResponse($booking_id, $upsells);
        }catch (\Exception $e){
            log_exception_by_exception_object($e, null, 'error');
            return null;
        }
    }

    public function getUpsellOrdersAndCart($id){

        $cart = UpsellCart::where('booking_info_id', $id);
        $upsell_cart_ids = $cart->pluck('upsell_id');
        $ids_with_persons = $cart->pluck('persons', 'upsell_id')->toArray();

        $cart_upsell = Upsell::with(['upsellType'])->whereIn('id', $upsell_cart_ids)->where('status', Upsell::STATUS_ACTIVE)->get();
        foreach($cart_upsell as $upsell){
            if(array_key_exists($upsell->id, $ids_with_persons)){
                $upsell->guest_count = $ids_with_persons[$upsell->id];
            }
        }
        return $this->getUpsellResponse($id, $cart_upsell);
    }

    private function getUpsellResponse($booking_info_id, $upsells_with_type){

        $data_set = [];
        $booking = BookingInfo::where('id', $booking_info_id)->with('guest_data')->first();
        $data_set['booking_info'] = $booking;
        $data_set['night_count'] = self::calculateStayNights($booking->full_response);
        $data_set['guest_count'] = (!empty($booking->guest_data) && !empty($booking->guest_data->adults)) ? $booking->guest_data->adults : $booking->num_adults;
        $data_set['amount_due'] = 0;

        foreach($upsells_with_type as $key => $upsell){

            $price_label = Config::get('db_const.upsell_listing.per.'.Config::get('db_const.upsell_listing.per.get_key.'.$upsell->per));
            $period_label = Config::get('db_const.upsell_listing.period.'.Config::get('db_const.upsell_listing.period.get_key.'.$upsell->period));
            $guests = !empty($upsell->guest_count) && $upsell->guest_count > 0 ? $upsell->guest_count : $data_set['guest_count'];

            if($period_label['value'] == 2){

                //$price_section = ($price_label['value'] == 2) ? ($data_set['night_count'].'x'.$guests.'x'.$upsell->value) : ($data_set['night_count'].'x'.$upsell->value);
                $item_price_total = ($price_label['value'] == 2) ? ($data_set['night_count'] * $guests * $upsell->value) : ($data_set['night_count'] * $upsell->value);
            }else{
                //$price_section = ($price_label['value'] == 2) ? ($guests .'x'. $upsell->value) : ($upsell->value);
                $item_price_total = ($price_label['value'] == 2) ? ($guests * $upsell->value) : ($upsell->value);
            }

            $data_set['amount_due'] += $item_price_total;
            $upsells_with_type[$key]->total_price = $item_price_total;
            $upsells_with_type[$key]->guest_count = $guests;
            $upsells_with_type[$key]->show_guest_count = $price_label['value'] == 2;
        }

        $data_set['upsell'] = $upsells_with_type;
        return $data_set;
    }

    /**
     * @param $upsell_id
     * @return mixed
     */
    public function countAttachedPropertiesWithUpsell($upsell_id)
    {
        return UpsellPropertiesBridge::where('upsell_id', $upsell_id)->count();
    }

    /**
     * @param $booking_info_id
     * @return mixed
     */
    public function upsellOrderList($booking_info_id)
    {
        try{

            /**
             * @var  $booking_info BookingInfo
             */
            $booking_info = BookingInfo::where('id', $booking_info_id)->first();
            $upsell_types = $this->getUpsellTypes($booking_info->user_account_id,false);
            $order_details = UpsellOrderDetail::whereIn('upsell_order_id', $booking_info->upsellOrders->pluck('id')->toArray())->with('UpsellOrder')->get();
            $currency_symbol = get_currency_symbol($booking_info->property_info->currency_code);
            $time_zone = $booking_info->property_info->time_zone;

            return $order_details->transform(function($item) use ($upsell_types, $currency_symbol, $time_zone, $booking_info) {

                $setting_copy = json_decode($item->upsell_price_settings_copy, true);
                $original_value = $setting_copy['value'];

                $setting_copy['value'] = $setting_copy['value_type'] ==  config('db_const.upsell_listing.value_type.percentage.value')
                    ? $setting_copy['value'] . '% of Booking Amount' : 'Flat Fee '.$currency_symbol . $setting_copy['value'];

                $setting_copy = convertTemplateVariablesToActualData(BookingInfo::class, $booking_info->id, $setting_copy);

                $type = $upsell_types->where('id', $setting_copy['upsell_type_id'])->first();
                $time_frame = $setting_copy['meta']['from_time'] . $setting_copy['meta']['from_am_pm'] .' to '.$setting_copy['meta']['to_time'] . $setting_copy['meta']['to_am_pm'];

                return [
                    'id' => $item->id,
                    'type' => !empty($type) ? $type->title : 'N/A',
                    'per' => get_config_column_values('upsell_listing', 'per', $setting_copy['per']),
                    'period' => get_config_column_values('upsell_listing', 'period', $setting_copy['period']),
                    'value_type' => get_config_column_values('upsell_listing', 'value_type', $setting_copy['value_type']),
                    'value' => $setting_copy['value'],
                    'time_frame' => $time_frame,
                    'is_time_set' => (($time_frame !== '00:00am to 00:00am') && ($time_frame !== '00:00pm to 00:00pm')),
                    'amount' => $currency_symbol . $item->amount,
                    'currency_symbol' => $currency_symbol,
                    'charge_ref_no' => $item->UpsellOrder->charge_ref_no,
                    'upsell_order_id' => $item->upsell_order_id,
                    'description'=> $setting_copy['meta']['description'],
                    'due_date' => Carbon::parse($item->created_at, 'GMT')->timezone($time_zone)->toDayDateTimeString(),
                    'original_value'=> $original_value,  //value copy for email
                    'original_amount'=> $item->amount,
                    'upsell_price' => $currency_symbol.upsell_price_for_booking($booking_info, $setting_copy['value_type'], $original_value),
                    'rules' => $setting_copy['meta']['rules'],
                    'payment_method' => [
                        'cc_last_4_digit' => $item->UpsellOrder->ccInfo->cc_last_4_digit,
                        'cc_exp_month' => $item->UpsellOrder->ccInfo->cc_exp_month,
                        'cc_exp_year' => $item->UpsellOrder->ccInfo->cc_exp_year,
                    ]
                ];
            });

        }catch (\Exception $e){
            log_exception_by_exception_object($e, null, 'error');
            return [];
        }
    }

    /** Guide Book Types Methods  */
    /**
     * @param array $data
     * @return bool
     */
    public function  createType(array $data)
    {
        $user = Auth::user();
        UpsellType::create([
            'user_id'=>$user->id,
            'user_account_id'=>$user->user_account->id,
            'is_user_defined'=>1,
            'title'=>$data['title'],
//            'icon'=>$data['icon'],
            'priority'=>$data['priority'],
            'status'=>$data['status']
        ]);
        return true;
    }

    /**
     * @param array $data
     * @return bool
     */
    public  function  updateType(array $data)
    {

        UpsellType::where('id',$data['serve_id'])->update([
            'title'=>$data['title'],
//            'icon'=>$data['icon'],
            'priority'=>$data['priority'],
            'status'=>$data['status']
        ]);
        return true;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function getOneType(int $id)
    {
        return UpsellType::where([
            ['user_account_id', auth()->user()->user_account_id],
            ['id', $id]
        ])->get();
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateTypeStatus(int $id, array $data){
        UpsellType::where('id',$id)->update($data);
        return true;
    }
}
