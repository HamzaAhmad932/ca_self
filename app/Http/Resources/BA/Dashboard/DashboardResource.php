<?php

namespace App\Http\Resources\BA\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use App\Traits\Resources\General\Dashboard;

class DashboardResource extends JsonResource
{
    use Dashboard;

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        try {
            $line = $this->lineGraphDaily();
            $pie = $this->pieGraphBookingSource($this->user_bookings_source->unique('booking_source_form_id'), $this->bookings_info);
            $sale = $this->totalSale();

            return [
                'all_properties' => $this->properties_info_count,
                'active_properties' => $this->active_properties,
                'all_booking_sources' => $this->user_bookings_source->unique('booking_source_form_id')->count(),
                'line' => [
                    'labels' => $line->pluck('label'),
                    'values' => $line->pluck('price')
                ],
                'pie' => [
                    'labels' => $pie->labels,
                    'values' => $pie->values
                ],
                'total_sale' => $sale,
                'client' => auth()->user(),
                'show_line_graph' => count($line) > 0 ? true : false
            ];
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['File' => __FILE__, 'Stack' => $e->getTraceAsString()]);
        }
        return [];
    }
}
