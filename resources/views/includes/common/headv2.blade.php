 <!-- begin::Head -->
    <head>
        <script>

            // window.addEventListener( "pageshow", function ( event ) {
            //     var historyTraversal = event.persisted ||
            //         ( typeof window.performance != "undefined" &&
            //             window.performance.navigation.type === 2 );
            //     if ( historyTraversal ) {
            //         // Handle page restore.
            //         alert("Testing Testing");
            //         window.location.reload();
            //     }
            // });

            // let backButtonCheck = performance.getEntriesByType('navigation');
            //
            // for(var i = 0; i < backButtonCheck.length; i++) {
            //     var type = String(backButtonCheck[i].type);
            //     if(type === "back_forward" && type !== "undefined"){
            //         alert("Testing Testing");
            //         window.location.reload();
            //     }
            // }

            if (String(window.performance.getEntriesByType("navigation")[0].type) === "back_forward") {
                window.location.reload()
            }

        </script>
        <meta charset="utf-8" />
        
       
        <meta name="description" content="Latest updates and statistic charts">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="user-id" content="{{ Auth::check() ? Auth::user()->id : '' }}">
         <title>@yield('page-title')</title>
        <!--begin::Web font -->
        
         {{--<script src="{{ asset('assets/app/js/webfont.js') }}"></script>
        <script>
        WebFont.load({
        google: {"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700"]},
        active: function() {
        sessionStorage.fonts = true;
        }
        });
        </script>--}}
        <!--end::Base Styles -->
        <link rel="shortcut icon" href="{{ asset('images/favicon-icon.png') }}" />
        
        {{--<link href="{{ asset('assets/vendors/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/vendors/base/vendors.bundle.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/demo/default/base/style.bundle.css') }}" rel="stylesheet" type="text/css" />

        <link rel="stylesheet" href="{{ asset('assets/card.css')}}">
        <link rel="stylesheet" href="{{ asset('assets/card_form.css')}}">--}}
        {{--<link href="{{ asset('assets/vendors/base/vendors.bundle.css') }}" rel="stylesheet" type="text/css" />--}}
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">

        <link rel="stylesheet" href="{{ asset('assets/v2/css/bootstrap-theme.css')}}">
        <link rel="stylesheet" href="{{ asset('assets/v2/css/style.css')}}">
        <link rel="stylesheet" href="{{ asset('assets/v2/css/custom_old.css')}}">
        <link rel="stylesheet" href="{{ asset('assets/card.css')}}">
        <link rel="stylesheet" href="{{ asset('assets/card_form.css')}}">

    </head>
    <!-- end::Head -->
