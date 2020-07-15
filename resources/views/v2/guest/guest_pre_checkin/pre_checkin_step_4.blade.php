@extends('v2.guest.app_pre_checkin')

@section('page_content')
    {{ load_component(GUEST_PreCheckin_AddOnServices_Page, $booking_id, ["booking_id" => $booking_id]) }}
{{--    <add-on-services booking_id="{{$booking_id}}"></add-on-services>--}}
@endsection()
