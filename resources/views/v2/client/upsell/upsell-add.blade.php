@extends('v2.client.app')
@push('below_css')
@endpush
@section('page_content')
    <div class="page-content">
        {{ load_component(Client_Upsell_Add_Page, auth()->user(), ["serve_id"=>"0"]) }}
    </div>
@endsection
@push('below_script')
@endpush