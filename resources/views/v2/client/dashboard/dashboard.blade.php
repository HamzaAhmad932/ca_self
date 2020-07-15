@extends('v2.client.app')
@push('below_css')
    <link href="{{ asset('v2/css/chartist.css') }}" rel="stylesheet">
@endpush
@section('page_content')

    {{ load_component(Client_Dashboard_Page, auth()->user()) }}

@endsection
@push('below_script')
@endpush
