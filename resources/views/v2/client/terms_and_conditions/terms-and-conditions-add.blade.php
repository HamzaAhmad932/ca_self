@extends('v2.client.app')
@push('below_css')
@endpush
@section('page_content')
        {{ load_component(Client_Terms_And_Conditions_Add_Page, auth()->user(), []) }}
@endsection
@push('below_script')
@endpush