@extends('layouts.admin')

@section('content')
    <div id="app">
        <user-list user_account_id="{{$user_account_id}}"></user-list>
    </div>
@endsection

@section('ajax_script')

@endsection
