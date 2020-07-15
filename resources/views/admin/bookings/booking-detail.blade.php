@extends('layouts.admin')

@section('content')
    <div id="app">
        <admin-booking-detail booking_info_id="{{$booking_info_id}}"></admin-booking-detail>
    </div>
@endsection

@section('ajax_script')

@endsection
