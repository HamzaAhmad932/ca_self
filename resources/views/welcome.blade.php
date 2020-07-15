<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>
        <link rel="shortcut icon" href="{{ asset('images/favicon-icon.png') }}" />

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .content {
                text-align: center;
                color: #fff;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }

            .register-button {
                display: inline-block;
                margin-bottom: 0;
                text-align: center;
                vertical-align: middle;
                -ms-touch-action: manipulation;
                touch-action: manipulation;
                cursor: pointer;
                background-image: none;
                white-space: nowrap;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
                font-weight: 600;
                padding: 18px 40px 18px 40px;
                border: none;
                border-radius: 3px;
                color: #fff;
                font-size: 16px;
                line-height: 1.6;
                letter-spacing: 1px;
                text-transform: uppercase;
                -webkit-transition: none;
                transition: none;
                background-color: #00a5f9;
                background-repeat: repeat-x;
                background-size: contain;
                -webkit-box-shadow: inset 0 -3px 0 rgba(0, 0, 0, 0.3);
                box-shadow: inset 0 -3px 0 rgba(0, 0, 0, 0.3);
                text-decoration: none;
            }

            .intro {
                font-size: 3.75rem;
                line-height: 5rem;
                text-align: center;
            }

            .white-text {
                color: #fff;
            }

            .header-style-one .intro-section h5 {
                margin-top: 13px;
                margin-bottom: 70px;
            }
            #intro_section_text_2 {
                margin-top: 30px;
                margin-bottom: 40px;
                font-size: 18px;
                text-align: center;
            }

            .alert-box{
                
            }

        </style>


    </head>
    <body>

        <div class="flex-center position-ref full-height" style="background: url('images/bg_main.jpg'); background-position: center; background-position-y: top;">
            <div style="background: rgba(0, 0, 0, 0.7); height: 100%; width: 100%; position: absolute;"></div>

            <div class="content" style="position:absolute;">

                <img src="images/favicon.png" alt="{{ config('app.name') }}"/>

                <h2 id="intro_section_text_1" class="intro white-text">Powerful Payment Processing</h2>

                <h5 id="intro_section_text_2" class="white-text">Automate your credit card charging process for all your bookings. For hotels, B&amp;B's, holiday homes, vacation rentals, hostels, agencies.</h5>

                @if (Route::has('login'))
                    <div>
                        @auth
                            @hasanyrole('superAdmin|admin')
                            <a class="register-button" href="{{ url('admin/dashboard') }}">Dashboard</a>
                        @else
                            <a class="register-button" href="{{ url('client/v2/dashboard') }}">Dashboard</a>

                            @endrole

                            @else
                                <a class="register-button" href="{{ route('login') }}">{{ __('main/text.login') }}</a> &nbsp;&nbsp;&nbsp;
                                <a class="register-button" href="{{ route('register') }}">{{ __('main/text.register') }}</a>

                                <br>
                                <h5 id="intro_section_text_2" class="white-text alert-box">For Charge Automation Version 1, please click <a href="https://chargeautomation.com/root_app/login.php" style="color:#00a5f9;">here</a></h5>

                            @endauth
                    </div>
                @endif

            </div>

        </div>

        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-124409336-1"></script>
        <script src="{{ asset('v2/js/google_analytics_code.js') }}"></script>
    </body>
</html>
