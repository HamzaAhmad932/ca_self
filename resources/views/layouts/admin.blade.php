<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
    @include('includes.common.head')
    
    <!-- begin::Body -->
    <body  class="m-page--fluid m--skin- m-content--skin-light2 m-header--fixed m-header--fixed-mobile m-aside-left--enabled m-aside-left--skin-dark m-aside-left--fixed m-aside-left--offcanvas m-footer--push m-aside--offcanvas-default"  >
        <!-- begin:: Page -->
            <div class="m-grid m-grid--hor m-grid--root m-page">

                @include('includes.admin.header')

                <!-- begin::Body -->
                <div class="m-grid__item m-grid__item--fluid m-grid m-grid--ver-desktop m-grid--desktop m-body">

                    @include('includes.admin.leftnav')

                    <div class="m-grid__item m-grid__item--fluid m-wrapper">

                            @yield('content')

                    </div>

                </div>

                        @include('includes.common.footer')

                    </div>
                <!-- end:: Page -->
                @include('includes.admin.rightbar')
                <!-- begin::Scroll Top -->
                <div id="m_scroll_top" class="m-scroll-top">
                    <i class="la la-arrow-up"></i>
                </div>


                <script type="text/javascript" src="{{ asset('js/app.js') }}"></script>
                @include('includes.common.common_base_script')
                @include('includes.common.dashboard_script')
                @yield('ajax_script')










                
            </body>
            <!-- end::Body -->
        </html>