@extends('v2.client.app')
@push('below_css')
	<style type="text/css">
		div.dataTables_paginate.paging_simple_numbers { float: right;padding-right: 2em; }
		div.dataTables_info { padding-left: 2em;padding-top: 2em; }
	</style>
@endpush

@section('page_content')
    <manageteam></manageteam>
@endsection

@push('below_script')
@endpush