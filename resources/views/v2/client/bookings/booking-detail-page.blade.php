@extends('v2.client.app')
@push('below_css')

@endpush
@section('page_content')

    {{ load_component(Client_Booking_Detail_Page, auth()->user(), [
        "booking_id"=>$booking_id,
        "pms_prefix"=>$pms_prefix
    ]) }}

@endsection
@push('below_script')
@endpush