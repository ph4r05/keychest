<head>
    <meta charset="UTF-8">
    <title> Keychest - @yield('htmlheader_title', 'Profile') </title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link rel="shortcut icon" type="image/png" href="/images/favicon.png">


    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="description" content="KeyChest - certificate expiry, certificate monitoring for TLS, HTTPS, Letsencrypt, with free cloud service. Automatic monitoring of subdomain servers as they are set up.">
    <meta name="author" content="Enigma Bridge Ltd, KeyChest">
    <meta name="keywords" content="letsencrypt, monitor, monitoring, tls, https, certificate expiry, certificate expiration, certificate monitoring, free monitoring">
    <meta name="title" content="Certificate expiry monitoring, KeyChest for HTTPS, TLS, Letsencrypt expiry and server status">

    <!-- Facebook -->
    <meta property="og:title" content="Certificate expiry monitoring, KeyChest for HTTPS, TLS, Letsencrypt expiry and server status" >
    <meta property="og:type" content="website" />
    <meta property="og:description" content="KeyChest - certificate expiry monitor, certificate monitoring, server status checker for TLS, HTTPS, Letsencrypt, with free cloud service. Automatic monitoring of subdomain servers as they are set up." >
    <meta property="og:url" content="{{ url('/home') }}" />
    <meta property="og:image" content="{{url('/images/keychest_centre_meta_clean.png') }}" />

    <!-- Twitter -->
    <meta name="twitter:card" content="landing_centre_clean">

    <meta name="twitter:title" content="Certificate expiry monitoring, KeyChest for HTTPS, TLS, Letsencrypt expiry and server status">
    <meta name="twitter:description" content="KeyChest - certificate expiry monitoring and server status checker for TLS, HTTPS, Letsencrypt, with free cloud service. Automatic monitoring of subdomain servers as they are set up.">
    <meta name="twitter:image" content="{{url('/images/keychest_centre_meta_clean.png') }}">




    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ mix('/css/all.css') }}" rel="stylesheet" type="text/css" />

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script>
        //See https://laracasts.com/discuss/channels/vue/use-trans-in-vuejs
        window.trans = @php
            // copy all translations from /resources/lang/CURRENT_LOCALE/* to global JS variable
            $lang_files = File::files(resource_path() . '/lang/' . App::getLocale());
            $trans = [];
            foreach ($lang_files as $f) {
                $filename = pathinfo($f)['filename'];
                $trans[$filename] = trans($filename);
            }
            $trans['adminlte_lang_message'] = trans('adminlte_lang::message');
            echo json_encode($trans);
        @endphp
    </script>

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

        // delete window.Promise;  // testing compatibility with polyfills
        // delete Promise;

    </script>
    <!-- Google Analytics END -->

    @yield('header-scripts')

</head>
