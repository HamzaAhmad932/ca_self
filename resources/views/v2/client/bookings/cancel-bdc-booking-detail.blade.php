<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
<head>
    @include('v2.guest.includes.head')
    @stack('below_css')
</head>
<body>
<div id="wrapper" class="guest-panel-wrapper cancel-bdc-booking">

    <div id="app">
        <div class="gp-header">
            <a class="company-logo" href="#">
                <img src="{{ asset('images/favicon.png') }}" alt="ChargeAutomation" style="max-height: initial;width:90px"/>
            </a>
            <div class="gp-nav">
                <h5>
                    {{ $data['bookingInfo']['guest_name'] }}&nbsp;
                    {{ $data['bookingInfo']['guest_last_name'] }}&nbsp;
                </h5>
            </div>
        </div>
        <cancel-bdc-booking-detail booking_id="{{ $data['bookingInfo']['id'] }}" booking_url="{{ $url }}"></cancel-bdc-booking-detail>
    </div>
</div>
@include('v2.guest.includes.footer_script')
@stack('below_script')
</body>
</html>