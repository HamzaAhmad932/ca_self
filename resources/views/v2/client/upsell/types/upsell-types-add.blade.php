@extends('v2.client.app')
@push('below_css')

@endpush
@section('page_content')
    {{ load_component(Client_Upsell_Type_Add_Page, auth()->user(), []) }}
@endsection
@push('below_script')
@endpush
