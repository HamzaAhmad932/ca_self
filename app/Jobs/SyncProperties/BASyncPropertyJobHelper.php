<?php


namespace App\Jobs\SyncProperties;


use App\Events\Emails\EmailEvent;
use App\Events\PropertyConnectStatusChangeEvent;
use App\Jobs\GatewayIntegrityCheckJob;
use App\PropertyInfo;
use App\RoomInfo;
use App\System\PMS\exceptions\PmsExceptions;
use App\System\PMS\Models\PmsOptions;
use App\System\PMS\Models\Property;
use App\System\PMS\PMS;
use App\Unit;
use App\UserAccount;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

trait BASyncPropertyJobHelper
{
    /**
     * @param UserAccount $user_account
     * @param array $time_zones
     * @param array $pms_properties
     * @return array
     */
    public function updateBA_user_properties(UserAccount $user_account, array $time_zones = [], array $pms_properties = [])
    {

        /**
         * It is to collect names of such properties which have no Property Key assigned to them.
         * Purpose of it to display names on dashboard or email in form of error or information alert to client.
         */
        $key_missing_properties_string = '';

        $key_missing_properties_arr = array();
        $pms_available_properties = array();
        $properties_to_update_on_pms = array();
        $whiteListIpFlag = false;

        try {
            /**
             * @var Property $pms_property
             */
            foreach ($pms_properties as $key => $pms_property) {

                array_push($pms_available_properties, $pms_property->id);

                $property_info = $this->propertyInfoByPmsProperty($user_account, $pms_property);
                $property_info->country = get_country_name_full_by_code($pms_property->country);
                $property_info->currency_code = $pms_property->currencyCode;
                $property_info->last_sync = now()->toDateTimeString();
                $property_info->longitude = $pms_property->longitude;
                $property_info->latitude = $pms_property->latitude;
                $property_info->name = $pms_property->propertyName;
                $property_info->address = $pms_property->address;
                $property_info->city = $pms_property->city;
                $property_info->available_on_pms = 1;


                $property_info->time_zone = !empty($time_zones[$pms_property->ownerId])
                    ? $time_zones[$pms_property->ownerId]
                    : null;


                if ($pms_property->propertyKey != -1
                    && $pms_property->propertyKey != ''
                    && $pms_property->propertyKey != ' ') {
                    $property_info->property_key = $pms_property->propertyKey;
                } elseif ($pms_property->propertyKey == -1) {
                    $whiteListIpFlag = true;
                } else {
                    $key_missing_properties_string .= $pms_property->propertyName . "<br>";
                    array_push($key_missing_properties_arr, $pms_property->id);
                }


                $this->updateRentals($property_info, $pms_property);

                $this->verifyCaNotifyUrl(
                    $user_account,
                    $property_info, $pms_property,
                    $properties_to_update_on_pms
                );


                $this->checkPropertyGatewayConnectionIntegrity($user_account, $property_info);

                $property_info->save();
            }


            $this->removeUnavailableProperties($user_account, $pms_available_properties);

            $this->caNotifyUrlUpdate($user_account, $properties_to_update_on_pms);

        } catch (Exception $e) {
            log_exception_by_exception_object($e, ['UserAccount' => $user_account->id]);
        }


        // Send Email to inform If Properties received with empty Prop-key.
        $this->informForKeyMissingProperties($user_account, $key_missing_properties_arr);


        return [
            'names' => $key_missing_properties_string,
            'property_pms_ids' => $key_missing_properties_arr,
            'whiteListIpFlag' => $whiteListIpFlag
        ];

    }

    /**
     * @param UserAccount $userAccount
     * @param PropertyInfo $propertyInfo
     */
    public function checkPropertyGatewayConnectionIntegrity(UserAccount $userAccount, PropertyInfo $propertyInfo)
    {
        if ($propertyInfo->use_pg_settings != 0) { // Local PG Settings
            GatewayIntegrityCheckJob::dispatch($userAccount, $propertyInfo);
        }
    }

    /**
     * @param UserAccount $user_account
     * @param array $properties_to_update_on_pms
     */
    public function caNotifyUrlUpdate(UserAccount $user_account, array $properties_to_update_on_pms = [])
    {
        try {
            if (!empty($properties_to_update_on_pms)) {
                $pms = new PMS($user_account);
                $pmsOptions = new PmsOptions();
                $pmsOptions->requestType = PmsOptions::REQUEST_TYPE_XML;
                $pms->update_properties($pmsOptions, $properties_to_update_on_pms);
            }
        } catch (PmsExceptions $exception) {

            log_exception_by_exception_object($exception, ['propertiesToUpdateData' => $properties_to_update_on_pms]);
        }
    }


    /**
     * @param UserAccount $user_account
     * @param PropertyInfo $property_info
     * @param Property $pms_property
     * @param array $propertiesToUpdateData
     */
    public function verifyCaNotifyUrl(UserAccount $user_account, PropertyInfo &$property_info, Property $pms_property, array &$propertiesToUpdateData = [])
    {
        if ($property_info->status
            && $this->isCaNotifyUrlMismatch($property_info->notify_url, $pms_property->caNotifyURL)) {

            $notify_url = generatePropertyNotificationUrlForBookingAutomation($user_account->id);

            $property = new Property();
            $property->caNotifyURL = append_notify_url($pms_property, $notify_url);
            $property->action = Property::BA_ACTION_MODIFY;
            $property->id = $property_info->pms_property_id;
            $property->propertyKey = $property_info->property_key;

            array_push($propertiesToUpdateData, $property);
        }

    }

    /**
     * @param $url_from_db
     * @param $url_on_pms
     * @return bool
     */
    public function isCaNotifyUrlMismatch($url_from_db, $url_on_pms)
    {

        if (empty($url_from_db) || empty($url_on_pms))
            return true;

        foreach (explode("\n", $url_on_pms) as $single_url) {
            $parsed_url = parse_url($single_url);
            if (!empty($parsed_url['host']) && ($parsed_url['host'] == config('app.url_host'))) {
                return $url_from_db != html_entity_decode($single_url);
            }
        }

        return true;
    }


    /**
     * @param UserAccount $user_account
     * @param $key_missing_properties_arr
     */
    public function informForKeyMissingProperties(UserAccount $user_account, $key_missing_properties_arr)
    {
        if (!empty($key_missing_properties_arr)) {

            $property_info_ids = $user_account->activeProperties()
                ->whereIn('pms_property_id', $key_missing_properties_arr)->pluck('id')->toArray();

            if (!empty($property_info_ids)) {
                event(
                    new EmailEvent(
                        config('db_const.emails.heads.empty_property_key_received.type'),
                        $user_account->id,
                        ['properties_info_ids' => $property_info_ids]
                    )
                );
            }
        }
    }

    /**
     * @param UserAccount $user_account
     * @param Property $pms_property
     * @return PropertyInfo
     */
    public function propertyInfoByPmsProperty(UserAccount $user_account, Property $pms_property)
    {

        $property_info = $user_account->properties_info->where('pms_property_id', $pms_property->id)->first();

        if (is_null($property_info)) {
            $property_info = new PropertyInfo();
            $property_info->pms_property_id = $pms_property->id;
            $property_info->logo = 'no_image.png';
            $property_info->user_id = 0;
            $property_info->pms_id = $user_account->pms->pms_form_id;
            $property_info->user_account_id = $user_account->id;
            $property_info->user_payment_gateway_id = 0;
            $property_info->property_key = $pms_property->propertyKey;
            $property_info->status = 0;
            $property_info->save();

        }

        return $property_info;
    }


    /**
     * @param PropertyInfo $property_info
     * @param Property $pms_property
     */
    public function updateRentals(PropertyInfo $property_info, Property $pms_property)
    {

        $remote_room_ids = Arr::pluck($pms_property->rooms, 'id');
        $all_rooms = RoomInfo::where('property_info_id', $property_info->id)->get()->keyBy('pms_room_id');
        $db_room_ids = $all_rooms->keys()->all();
        $db_existed_rooms = $all_rooms;
        $bulk_rooms = array();

        foreach ($pms_property->rooms as $k1 => $room) {
            $existed_room = !empty($db_existed_rooms[$room->id]) ? $db_existed_rooms[$room->id] : null;
            if (empty($existed_room)) {
                array_push($bulk_rooms,
                    [
                        'name' => $room->roomName,
                        'property_info_id' => $property_info->id,
                        'pms_room_id' => $room->id,
                        'available_on_pms' => 1,
                        'created_at' => now()->toDateTimeString(),
                        'updated_at' => now()->toDateTimeString()
                    ]
                );

            } elseif ($existed_room->name != $room->roomName) {
                $existed_room->update(['name' => $room->roomName]);
            }


            if (!empty($bulk_rooms)) {
                RoomInfo::insert($bulk_rooms);
            }

            // Rental's units update
            $this->updateUnits($room, $property_info->id);
        }

        $this->removeUnavailableRentals($property_info->id, array_diff($db_room_ids, $remote_room_ids));
    }


    /**
     * @param $room
     * @param $property_info_id
     */
    public function updateUnits($room, $property_info_id)
    {

        if (!empty($room->unitNames)) {

            try {

                $db_units = Unit::where('property_info_id', $property_info_id)
                    ->where('pms_room_id', $room->id);

                $db_unit_names = $db_units->pluck('unit_name')->toArray();

                $remote_units = explode("\r\n", $room->unitNames);

                $difference = array_merge(
                    array_diff($db_unit_names, $remote_units),
                    array_diff($remote_units, $db_unit_names)
                );


                if (count($difference) > 0) {
                    $db_units->delete();
                    $bulk_units = [];

                    foreach ($remote_units as $k => $unit_name) {
                        array_push($bulk_units,
                            [
                                'unit_name' => $unit_name,
                                'room_id' => 0,
                                'pms_room_id' => $room->id,
                                'property_info_id' => $property_info_id,
                                'unit_no' => $k + 1,
                                'created_at' => now()->toDateTimeString(),
                                'updated_at' => now()->toDateTimeString()
                            ]
                        );
                    }

                    if (!empty($bulk_units)) {
                        Unit::insert($bulk_units);
                    }
                }

            } catch (Exception $e) {
                log_exception_by_exception_object($e);
            }
        }
    }


    /**
     * @param UserAccount $user_account
     * @param array $pms_available_properties
     */
    public function removeUnavailableProperties(UserAccount $user_account, array $pms_available_properties = [])
    {

        $unavailable_properties = array_diff(
            $user_account->properties_info->pluck('pms_property_id')->toArray(),
            $pms_available_properties
        );

        if (count($unavailable_properties)) {

            // Inform Client
            event(new EmailEvent(
                    config('db_const.emails.heads.properties_unavailable_on_pms.type'),
                    $user_account->id,
                    [
                        'property_status' => false,
                        'properties_info_ids' => $user_account->properties_info
                            ->whereIn('pms_property_id', $unavailable_properties)
                            ->where('status', 1)->pluck('id')->toArray()
                    ])
            );


            // Pause All Trans & Auth
            event(new PropertyConnectStatusChangeEvent($unavailable_properties, true, false));

            PropertyInfo::where('user_account_id', $user_account->id)->whereIn('pms_property_id', $unavailable_properties)
                ->update(['status' => 0, 'available_on_pms' => 0]);
        }
    }


    /**
     * @param $property_info_id
     * @param array $room_id_to_be_deleted
     */
    public function removeUnavailableRentals($property_info_id, array $room_id_to_be_deleted = [])
    {
        if (count($room_id_to_be_deleted) > 0) {
            RoomInfo::whereIn('pms_room_id', $room_id_to_be_deleted)
                ->where('property_info_id', $property_info_id)
                ->update(['available_on_pms' => 0]);
        }
    }

    /**
     * The Following Code Will Count For How Many Days in a Row BA_UNAUTHORIZED Exception is Caught.
     * If It Will Be Caught More Then Three Days In A Row User Account Status
     * Will Be Updated To Disabled By Admin
     * @param UserAccount $user_account
     * @param PmsExceptions $exception
     */
    public function handlePMSException(UserAccount $user_account, PmsExceptions $exception)
    {
        switch ($exception->getCode()) {
            case PmsExceptions::BA_UNAUTHORIZED:

                if ($user_account->count_unauthorized_property_sync == 0
                    || Carbon::now()->diffInHours($user_account->last_properties_sync_exception) >= 24) {

                    $user_account->update(
                        [
                            'count_unauthorized_property_sync' => $user_account->count_unauthorized_property_sync + 1,
                            'last_properties_sync_exception' => Carbon::now()->toDateTimeString(),
                            'last_properties_synced' => Carbon::now()->toDateTimeString(),
                            'status' => $user_account->count_unauthorized_property_sync >= 5
                                ? config('db_const.user_account.status.deactive.value')
                                : $user_account->status,
                        ]
                    );

                }

                // Sending notification to client PMS is not verified
                event(new EmailEvent(config('db_const.emails.heads.unable_to_contact_pms.type'), $user_account->id));

                break;
        }

        report($exception);
    }
}