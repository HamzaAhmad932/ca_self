@extends('v2.guest.app')

@section('page_content')

    {{ load_component(GUEST_GuestPortal_Page, $booking_id, ["booking_id" => $booking_id]) }}
{{--    <guest-portal booking_id="{{$booking_id}}"></guest-portal>--}}

@endsection()
@push('below_script')
    <script src="https://js.stripe.com/v3/"></script>
@endpush
