@extends('v2.guest.app_pre_checkin')

@section('page_content')
    {{ load_component(GUEST_PreCheckin_PhotoBooth_Page, $booking_id, [
        "booking_id" => $booking_id,
        "type"=> $type
    ]) }}
{{--    <photo-booth booking_id="{{$booking_id}}" type="{{$type}}"></photo-booth>--}}
@endsection()
