<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
  <head>
    @include('v2.client.includes.head')
    @stack('below_css')
    @if(Auth::user()->user_account->status != 1 || empty(auth()->user()->user_account->integration_completed_on) )
      <style>
        #wrapper #app .page-content{ padding-top: 13rem; }
        @media only screen and (min-width: 300px) and (max-width: 480px) {
          #wrapper #app .page-content{
            padding-top: 16rem;
          }
        }

      </style>
    @endif()
    <script>
      @auth
              window.Permissions = {!! json_encode(Auth::user()->allPermissions, true) !!};
      @else
              window.Permissions = [];
      @endauth
    </script>
  </head>
  <body>
    <div id="wrapper">
      <div id="app">
        @include('v2.client.includes.header')
        @yield('page_content')
        @include('v2.client.includes.notification-sidebar')
        @include('v2.client.includes.footer')
      </div>
    </div>
    @include('v2.client.includes.footer_script')
    @stack('below_script')
  </body>
</html>
