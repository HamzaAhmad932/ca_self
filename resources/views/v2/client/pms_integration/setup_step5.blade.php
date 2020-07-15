@extends('v2.client.app')
@section('page_content')
{{ load_component(Client_Account_Setup_Step5_Page, auth()->user()) }}
@endsection