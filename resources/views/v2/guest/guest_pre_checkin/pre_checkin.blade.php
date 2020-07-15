@extends('v2.guest.app_pre_checkin')

@section('page_content')
    {{ load_component(GUEST_PreCheckin_Page, $booking_id, ["booking_id" => $booking_id]) }}
{{--    <guest-pre-checkin booking_id="{{$booking_id}}"></guest-pre-checkin>--}}
@endsection()
