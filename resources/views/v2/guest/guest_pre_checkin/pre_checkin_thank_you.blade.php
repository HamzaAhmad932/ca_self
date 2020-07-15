@extends('v2.guest.app_pre_checkin')

@section('page_content')
    {{ load_component(GUEST_PreCheckin_ThankYou_Page, $booking_id, ["booking_id" => $booking_id, "next_url" => $next_url]) }}
{{--    <pre-checkin-thank-you booking_id="{{$booking_id}}" next_url="{{$next_url}}"></pre-checkin-thank-you>--}}
@endsection()
