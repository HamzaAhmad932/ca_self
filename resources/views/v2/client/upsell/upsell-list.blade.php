@extends('v2.client.app')
@push('below_css')

@endpush
@section('page_content')

    {{ load_component(Client_Upsell_List_Page, auth()->user(), ['disable_upsell'=> $disableUpSell ? 1 : 0]) }}

@endsection
@push('below_script')