<?php


namespace App\Http\Resources\General\Precheckin;


use App\BookingInfo;
use App\GuestImage;
use App\Traits\Resources\General\Precheckin;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class StepSevenSelfieResource extends JsonResource
{

    use Precheckin;
    private $booking_id;

    public function __construct($booking_id, $resource)
    {
        parent::__construct($resource);
        $this->booking_id = $booking_id;
    }

    public function toArray($request)
    {
        try {

            $meta = $this->getNextPageData(Config::get('db_const.pre_checkin.step_6'), $this->booking_id);

            $bookingInfo = BookingInfo::find($this->booking_id);
            $guestImage = $bookingInfo->guest_images->where('type', GuestImage::TYPE_SELFIE)->first();

            return [
                'status' => true,
                'status_code' => 200,
                'meta' => $meta,
                'selfie' => $guestImage == null ? '' : GuestImage::PATH_IMAGES . $guestImage->image
            ];
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['File' => __FILE__, 'Function' => __FUNCTION__, 'Stack' => $e->getTraceAsString()]);
        }

        return [
            'status' => true,
            'status_code' => 400,
            'meta' => ''
        ];

    }

}
