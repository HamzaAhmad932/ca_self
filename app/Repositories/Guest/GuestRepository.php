<?php


namespace App\Repositories\Guest;


use App\BookingInfo;
use App\GuestData;
use App\GuestImage;
use App\GuideBook;
use App\GuideBookType;
use App\PropertyInfo;
use App\Repositories\Settings\ClientGeneralPreferencesSettings;
use App\Repositories\Upsells\UpsellRepository;
use App\Repositories\GuideBooks\GuideBooksRepository;
use App\TermsAndCondition;
use App\TransactionInit;

class GuestRepository implements GuestInterface
{

    public function __construct()
    {

    }

    public function getGuestDetail(int $booking_id)
    {
        return BookingInfo::with('guest_data')->where('id', $booking_id)->first();
    }

    public function guestDataByBookingId(int $booking_id)
    {
        return GuestData::where('booking_id', $booking_id)->first();
    }

    public function getGuestImagesByBookingId(int $booking_id)
    {
        return GuestImage::where('booking_id', $booking_id)->latest()->get();
    }

    public function getCreditCardAndAuthOfBooking(int $booking_id)
    {
        return BookingInfo::where('id', $booking_id)->with(['cc_Infos', 'credit_card_authorization'])->first();
    }

    public function getGuestDataAndGuestImagesByBookingId(int $booking_id)
    {
        return BookingInfo::with(['cc_Infos', 'guest_images', 'guest_data'])->where('id', $booking_id)->first();
    }

    public function getGuestDataAndGuestImagesAndTransactionsByBookingId(int $booking_id)
    {
        $booking_info = BookingInfo::with([
            'cc_Infos',
            'guest_images',
            'guest_data',
            'credit_card_authorization',
            'transaction_init',
            //'room_info'
        ])
            ->where('id', $booking_id)->first();
        $booking_info->room_info;

        $property_info = $booking_info->property_info;
        $guide_books_repository = new GuideBooksRepository();
        $upsell_repository = new UpsellRepository();

        $check_guide_book_permission = new ClientGeneralPreferencesSettings($booking_info->user_account_id);
        $guide_book_permission = $check_guide_book_permission->isActiveStatus(config('db_const.general_preferences_form.guideBooks'), $booking_info->bookingSourceForm);
        $booking_info->guide_book_types = [];

        if ($guide_book_permission == 1) {
            $booking_info->guide_book_types = $guide_books_repository->getGuideBooksByPropertyAndRoomIds($booking_info->user_account_id, $property_info->id, $booking_info->room_info->id);
        }

        $booking_info->booking_upsells = $upsell_repository->upsellOrderList($booking_id);

        return $booking_info;
    }
}
