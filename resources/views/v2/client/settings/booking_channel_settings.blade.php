@extends('v2.client.app')
@section('page_content')
    <booking-channel-settings></booking-channel-settings>
@endsection
@push('below_script')
    <script src="{{ asset('v2/js/typeahead.bundle.min.js') }}" defer></script>
    <script src="{{ asset('v2/js/bootstrap-tokenfield.js') }}" defer></script>
@endpush