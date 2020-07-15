@extends('v2.guest.app_pre_checkin')
@section('page_content')
    {{ load_component(GUEST_PreCheckin_Summary_Page, $booking_id, ["booking_id" => $booking_id]) }}
{{--    <summary-step booking_id="{{$booking_id}}"></summary-step>--}}
@endsection()
@push('below_script')
    <script type="application/javascript" defer src="https://js.stripe.com/v3/"></script>
@endpush
