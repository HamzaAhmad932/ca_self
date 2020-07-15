<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
<head>
    @include('v2.guest.includes.head')
    @stack('below_css')
</head>
<body>
<div id="wrapper" class="guest-panel-wrapper">
    <div id="app">
        <guest-header
                email="{{$header['email']}}"
                property_name="{{$header['property_name']}}"
                is_chat_active="{{$header['is_chat_active']}}"
                tel="{{$header['tel']}}"
                booking_id="{{$header['booking']->id}}"
        ></guest-header>
        <guest-chat-panel calling_id="chat_panel_right"></guest-chat-panel>
        @yield('page_content')
        @include('includes.common.guest_footer')
    </div>
</div>
@include('v2.guest.includes.footer_script')
@stack('below_script')
</body>
</html>


{{--        @include('v2.guest.includes.header')--}}

{{--        <guest-chat-panel calling_id="chat-panel-header" booking_id="{{$booking_id}}"></guest-chat-panel>--}}
{{--        @include('v2.client.includes.notification-sidebar')--}}
{{--        @include('v2.client.includes.footer')--}}
