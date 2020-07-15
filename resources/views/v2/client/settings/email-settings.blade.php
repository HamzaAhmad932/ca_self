@extends('v2.client.app')
@section('page_content')
	<email-types-nav></email-types-nav>
@endsection
@push('below_script')
	<script src="{{ asset('v2/js/typeahead.bundle.min.js') }}" defer></script>
	<script src="{{ asset('v2/js/bootstrap-tokenfield.js') }}" defer></script>
@endpush