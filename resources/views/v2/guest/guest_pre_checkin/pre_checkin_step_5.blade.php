@extends('v2.guest.app_pre_checkin')

@section('page_content')
    {{ load_component(GUEST_PreCheckin_CreditCard_Page, $booking_id, [
        "booking_id" => $booking_id,
        "pms_prefix"=> $pms_prefix
    ]) }}
{{--    <credit-card-step booking_id="{{$booking_id}}"></credit-card-step>--}}
@endsection()
@push('below_script')
        <script src="https://js.stripe.com/v3/"></script>
@endpush