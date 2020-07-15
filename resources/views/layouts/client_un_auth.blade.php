<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
@include('includes.common.head')

<!-- begin::Body -->
<body  class="m-page--fluid m--skin- m-content--skin-light2  m-footer--push  m-scroll-top--shown "  >

<!-- begin:: Page -->
<div class="m-grid m-grid--hor m-grid--root m-page">

<!-- begin::Body -->
    <div class="m-grid__item m-grid__item--fluid m-grid m-grid--ver-desktop m-grid--desktop m-body">

        <div class="m-grid__item m-grid__item--fluid m-wrapper">

            @yield('content')

        </div>

    </div>

    @include('includes.common.footer')

</div>
<!-- end:: Page -->


<!-- begin::Scroll Top -->
<div id="m_scroll_top" class="m-scroll-top">
    <i class="la la-arrow-up"></i>
</div>


<!-- baseScript -->
@include('includes.common.common_base_script')
@include('includes.common.dashboard_script')
@yield('ajax_script')

<script type="text/javascript">

    var tzZZZ = moment.tz.guess();

</script>

</body>
</html>