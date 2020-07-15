<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
@include('includes.common.headv2')

<!-- <link rel="stylesheet" href="{{ asset('assets/card.css')}}"> -->

<!-- begin::Body -->
<body>
<!-- Chat-->
    
                @yield('content')

<!-- end:: Page -->

<div id='app'>
{{--    @include('includes.guest.rightbarv2')--}}
    {{--<div id="m_scroll_top" class="m-scroll-top">
        <i class="la la-arrow-up"></i>
    </div>--}}
</div>

<!-- baseScript -->

@include('includes.common.common_base_scriptv2')
@include('includes.common.dashboard_scriptv2')
<script type="text/javascript">
    Dropzone.autoDiscover = false;
</script>
@yield('ajax_script')



{{--@include('includes.client.axiosRequests')--}}
<!--end::Page Snippets -->



</body>
<!-- end::Body -->
</html>
