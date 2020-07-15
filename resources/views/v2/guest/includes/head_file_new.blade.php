<script>
    if (String(window.performance.getEntriesByType("navigation")[0].type) === "back_forward") {
        window.location.reload()
    }
</script>

<meta charset="utf-8">
<meta name="description" content="Latest updates and statistic charts">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="user-id" content="{{ Auth::check() ? Auth::user()->id : '' }}">
<link rel="shortcut icon" type="image/png" href="{{ asset('images/favicon-icon.png') }}" />

<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">





<!-- Bootstrap CSS-->
<link rel="stylesheet" href="{{ asset('v2/css/bootstrap-theme.css') }}">
<link rel="stylesheet" href="{{ asset('v2/css/guest-app.css') }}">

<!-- Styles -->
<link href="{{ asset('css/app.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/v2/css/custom.css')}}">


<!-- Scripts -->
<script src="{{ asset('js/app.js') }}" defer></script>

<title>{{$title}} </title>

<style>
    .StripeElement {
        box-sizing: border-box;

        height: 40px;

        padding: 10px 12px;

        border: 1px solid transparent;
        border-radius: 4px;
        background-color: white;

        box-shadow: 0 1px 3px 0 #e6ebf1;
        -webkit-transition: box-shadow 150ms ease;
        transition: box-shadow 150ms ease;
    }

    .StripeElement--focus {
        box-shadow: 0 1px 3px 0 #cfd7df;
    }

    .StripeElement--invalid {
        border-color: #fa755a;
    }

    .StripeElement--webkit-autofill {
        background-color: #fefde5 !important;
    }
</style>