@extends('v2.guest.app_pre_checkin')

@section('page_content')
    {{ load_component(GUEST_PreCheckin_Verification_Page, $booking_id, ["booking_id" => $booking_id]) }}
{{--    <verification-step booking_id="{{$booking_id}}"></verification-step>--}}
@endsection()
