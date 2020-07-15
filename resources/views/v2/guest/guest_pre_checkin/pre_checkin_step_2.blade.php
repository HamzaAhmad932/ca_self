@extends('v2.guest.app_pre_checkin')

@section('page_content')
    {{ load_component(GUEST_PreCheckin_ArrivalInfo_Page, $booking_id, ["booking_id" => $booking_id]) }}
{{--    <arrival-info-step booking_id="{{$booking_id}}"></arrival-info-step>--}}
@endsection()
