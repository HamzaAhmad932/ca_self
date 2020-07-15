    <script>
        if (String(window.performance.getEntriesByType("navigation")[0].type) === "back_forward") {
            window.location.reload()
        }
    </script>
    <script>
        window.myToken =  <?php echo json_encode([
            'csrfToken' => csrf_token(),
        ]); ?>
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
    <link rel="stylesheet" href="{{ asset('v2/css/style.css') }}">
    <link rel="stylesheet" href="{{asset('v2/css/animate.css')}}">
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <title> <?php echo pageTitle(Request::route()->getName()); ?> </title>
