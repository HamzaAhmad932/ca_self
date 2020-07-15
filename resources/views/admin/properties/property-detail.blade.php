@extends('layouts.admin')

@section('content')
    <div id="app">
        <property-detail property_info_id="{{$property_info_id}}"></property-detail>
    </div>
@endsection

@section('ajax_script')

@endsection
