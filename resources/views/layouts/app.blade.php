<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/png" href="/images/favicon.png">

    <meta name="description" content="KeyChest - certificate monitoring for TLS, HTTPS, Letsencrypt, with free cloud service. Automatic monitoring of subdomain servers as they are set up.">
    <meta name="author" content="Enigma Bridge Ltd, KeyChest">

    <meta property="og:title" content="KeyChest certificate monitoring - HTTPS, TLS, Letsencrypt" />
    <meta property="og:type" content="website" />
    <meta property="og:description" content="KeyChest - certificate monitoring for TLS, HTTPS, Letsencrypt, with free cloud service. Automatic monitoring of subdomain servers as they are set up." />
    <meta property="og:url" content="https://keychest.net" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Keychest') }} - certificate (expiry) monitoring HTTPS, TLS, Letsencrypt</title>

    <!-- Styles -->
    <link href="{{ mix('css/all-landing.css') }}" rel="stylesheet">

    <!-- Scripts -->
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
            'authGuest' => Auth::guest(),
            'urlBase' => url('/'),
            'urlLogin' => route('login'),
            'urlRegister' => route('register'),
            'urlLogout' => route('logout'),
            'urlFeedback' => route('rfeedback'),
            'authUserName' => Auth::guest() ? null : Auth::user()->name,
            'authUserId' => Auth::guest() ? null : Auth::user()->getAuthIdentifier(),
            'userTz' => Auth::guest() ? null : Auth::user()->timezone,
        ]) !!};
    </script>

    <!-- Google Analytics -->
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-84597687-4', 'auto');
        ga('send', 'pageview');

    </script>
    <!-- Google Analytics END -->

    <script src="https://donorbox.org/install-popup-button.js" type="text/javascript" defer></script>

</head>
<body>
    <div id="app" class="page-container search-page">
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        <img src="/images/logo2-rgb_keychest.png" height="30" title="{{ config('app.name', 'Keychest') }}">
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        &nbsp;
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right">

                        <!-- component navigation -->
                        @yield('content-nav')

                        <!-- general navigation -->
                        @include('partials.landing.navbar')
                    </ul>
                </div>
            </div>
        </nav>

        @yield('content')

    </div>

    <div class="ph4-modal">
        <div class="ph4-modal-wrap"></div>
    </div>

    <!-- Scripts -->
    <script src="{{ mix('/js/manifest.js') }}"></script>
    <script src="{{ mix('/js/vendor.js') }}"></script>
    <script src="{{ url (mix('/js/polyapp.js')) }}"></script>
    <script src="{{ mix('/js/misc.js') }}"></script>

    {!! survivor() !!}

</body>
</html>
