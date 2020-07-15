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
<title> <?php echo pageTitle(Request::route()->getName()); ?> </title>
<link href="{{ asset('css/auth.css') }}" rel="stylesheet">
<script src="{{ asset('js/app.js') }}" defer></script>
