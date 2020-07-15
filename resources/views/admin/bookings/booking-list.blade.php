@extends('layouts.admin')

@section('content')
    <div id="app">
        <admin-booking-list-page user_account_id="{{$user_account_id}}"></admin-booking-list-page>
    </div>
@endsection

@section('ajax_script')

@endsection
