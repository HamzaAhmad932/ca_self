 <!-- begin::Head -->
    <head>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-110454673-1"></script>
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-110454673-1');
        </script>
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


        {{--<title>{{ config('app.name', 'Charge Automation') }}</title>--}}



        <title> <?php echo pageTitle(Request::route()->getName()); ?> </title>

        <!--begin::Web font -->
        <script src="{{ asset('assets/app/js/webfont.js') }}"></script>
        <script>
        WebFont.load({
        google: {"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700"]},
        active: function() {
        sessionStorage.fonts = true;
        }
        });
        </script>
        <!--end::Web font -->
        <link rel="stylesheet" href="{{asset('introjs.css')}}">
        <!--begin::Page Vendors Styles -->
        <link href="{{ asset('assets/vendors/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet" type="text/css" /> <!--RTL version:<link href="assets/vendors/custom/fullcalendar/fullcalendar.bundle.rtl.css" rel="stylesheet" type="text/css" />-->
        <!--end::Page Vendors Styles -->
        <link href="https://fonts.googleapis.com/css?family=Roboto+Slab" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Roboto|Roboto+Slab" rel="stylesheet">

        <!--begin::Base Styles -->
        <link href="{{ asset('assets/vendors/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
         <link href="{{ asset('assets/vendors/base/vendors.bundle.css') }}" rel="stylesheet" type="text/css" /><!--RTL version:<link href="assets/vendors/base/vendors.bundle.rtl.css" rel="stylesheet" type="text/css" />-->
          <link href="{{ asset('assets/demo/default/base/style.bundle.css') }}" rel="stylesheet" type="text/css" /> <!--RTL version:<link href="assets/demo/default/base/style.bundle.rtl.css" rel="stylesheet" type="text/css" />-->
        <!--end::Base Styles -->
        <link rel="shortcut icon" href="{{ asset('images/favicon-icon.png') }}" />
        <!-- <link href="{{ asset('css/app.css') }}" />  -->
        <style>
            .m-subheader{
                padding-top: 15px !important;
            }
            .m-content{
                padding-top: 15px !important;
            }
            .wordwrap {    
                word-wrap: break-word;
                }
        </style>
        
        <link rel="stylesheet" href="{{ asset('assets/card.css')}}">
        <link rel="stylesheet" href="{{ asset('assets/card_form.css')}}">
        <link rel="stylesheet" href="{{asset('assetv2/css/intlTelInput.min.css')}}"/>
    </head>
    <!-- end::Head -->
