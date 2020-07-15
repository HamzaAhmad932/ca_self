@extends('v2.client.app')
@section('page_content')
    <pms-setup-step3></pms-setup-step3>
@endsection
@push('below_script')
    <script>
        $( document ).ready(function() {
            if ('{{Session::has('stripeConnectMsg')}}') {
                if ('{{(Session::get('stripeConnectMsg') === 'verified')}}')
                    toastr.success('Successfully connected to Stripe');
                else
                    toastr.error('{!! Session::get("stripeConnectMsg") !!}');
                '{{session()->forget("stripeConnectMsg")}}';
            }
        });
    </script>
@endpush