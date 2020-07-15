@extends('v2.client.app')
@push('below_css')
@endpush
@section('page_content')

    {{ load_component(Client_Property_List_Page, auth()->user()) }}

@endsection
@push('below_script')
    <!--<script src='https://kit.fontawesome.com/a076d05399.js'></script>-->
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



