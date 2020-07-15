<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
    <head>
        @include('v2.auth.includes.head')
        @stack('below_css')
        <script>
            window.front_end_terms = "{{ config('db_const.auth_keys.front_end_terms') }}"
        </script>
    </head>
    <body>
        <div class="m--login__signin_wrapper d-block float-left w-100">
            <div class="m--login__signin_container d-block overflow-hidden">
                <div class="alert-outer full-row">
                    @if(session('alerts'))
                        <div style="padding: 0 15px;" class="alert alert-custom alert-{{session('alerts.cls')}} fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
                            {{ session('alerts.message') }}
                        </div>
                    @endif
                </div>
                <div class="m__logo full-row">
                    <a href="{{ config('db_const.auth_keys.front_end_website') }}">
                        <img src="{{asset('images/favicon.png')}}">
                    </a>
                </div>
                <div id="app">
                    @yield('page_content')
                </div>
            </div>
        </div>
        @include('v2.auth.includes.footer_script')
        @stack('below_script')
    </body>
</html>