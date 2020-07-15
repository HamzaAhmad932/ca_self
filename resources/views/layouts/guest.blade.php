<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
@include('includes.common.head')

<link rel="stylesheet" href="{{ asset('assets/card.css')}}">

<!-- begin::Body -->
<body  class="m-page--fluid m--skin- m-content--skin-light2 m-header--fixed m-header--fixed-mobile m-aside-left--enabled m-aside-left--skin-dark m-aside-left--fixed m-aside-left--offcanvas m-footer--push m-aside--offcanvas-default"  >

<!-- begin:: Page -->
<div class="m-grid m-grid--hor m-grid--root m-page">




<!-- begin::Body -->






                @yield('content')





</div>

<!-- end:: Page -->
<div id='app'>
@include('includes.guest.rightbar')
<!-- begin::Scroll Top -->
<div id="m_scroll_top" class="m-scroll-top">
    <i class="la la-arrow-up"></i>
</div>
</div>
<!-- end::Scroll Top -->            <!-- begin::Quick Nav -->
<!-- <ul class="m-nav-sticky" style="margin-top: 30px;">
    <li class="m-nav-sticky__item" data-toggle="m-tooltip" title="Purchase" data-placement="left">
        <a href="https://themeforest.net/item/metronic-responsive-admin-dashboard-template/4021469?ref=keenthemes" target="_blank"><i class="la la-cart-arrow-down"></i></a>
    </li>
    <li class="m-nav-sticky__item" data-toggle="m-tooltip" title="Documentation" data-placement="left">
        <a href="https://keenthemes.com/metronic/documentation.html" target="_blank"><i class="la la-code-fork"></i></a>
    </li>
    <li class="m-nav-sticky__item" data-toggle="m-tooltip" title="Support" data-placement="left">
        <a href="https://keenthemes.com/forums/forum/support/metronic5/" target="_blank"><i class="la la-life-ring"></i></a>
    </li>
</ul> -->
<!-- begin::Quick Nav -->
<!-- baseScript -->
@include('includes.common.common_base_script')
@include('includes.common.dashboard_script')
@yield('ajax_script')





{{--@include('includes.client.axiosRequests')--}}
<!--end::Page Snippets -->



</body>
<!-- end::Body -->
</html>