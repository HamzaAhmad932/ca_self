<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
<head>
    @include('admin.stripe_commission_billing.guest.includes.head')
    @stack('below_css')
</head>
<body>
<div id="wrapper">
    <div id="app">
        @include('admin.stripe_commission_billing.guest.includes.header')
        @yield('page_content')
        @include('v2.client.includes.notification-sidebar')
        @include('v2.client.includes.footer')
    </div>
</div>
@include('v2.client.includes.footer_script')
@stack('below_script')
</body>
</html>