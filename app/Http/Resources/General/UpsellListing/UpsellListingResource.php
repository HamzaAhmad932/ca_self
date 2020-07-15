<?php

namespace App\Http\Resources\General\UpsellListing;


use App\Traits\Resources\General\UpsellListingHelperTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class UpsellListingResource extends JsonResource
{
    use UpsellListingHelperTrait;
    static $config_file = 'upsell_listing';

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => sprintf("%'.04d", $this->id),
            'value' => $this->value,
            'type' => ucwords($this->upsellType->title),
            'internal_name' => $this->internal_name,
            'per' => get_config_column_values(self::$config_file, 'per', $this->per),
            'period' => get_config_column_values(self::$config_file, 'period', $this->period),
            'status' => get_config_column_values(self::$config_file, 'status', $this->status),
            'value_type' => get_config_column_values(self::$config_file, 'value_type', $this->value_type),
            'meta' => $this->meta,
            'attached_rentals' => $this->attachedRentals(),
            'attached_rentals_count' => $this->upsellPropertiesBridge->where('status', config('db_const.upsell_listing.status.active.value'))->count(),
        ];
    }
}
