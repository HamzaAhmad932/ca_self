@extends('v2.guest.app_pre_checkin')

@section('page_content')
    {{ load_component(GUEST_PreCheckin_BasicInfo_Page, $booking_id, ["booking_id" => $booking_id]) }}
{{--    <basic-info-step booking_id="{{$booking_id}}"></basic-info-step>--}}
@endsection()
