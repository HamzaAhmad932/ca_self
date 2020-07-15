@extends('layouts.admin')

@section('content')
    <div id="app">
        <property-list user_account_id="{{$user_account_id}}"></property-list>
    </div>
@endsection

@section('ajax_script')

@endsection
