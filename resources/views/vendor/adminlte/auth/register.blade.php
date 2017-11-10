@extends('adminlte::layouts.auth')

@section('htmlheader_title')
    Register
@endsection

@section('content')

<body class="hold-transition register-page">
    <div id="app" v-cloak>
        <div class="register-box">
            <div class="register-logo" style="background: #fff; opacity: 0.6;" >
                <a href="{{ url('/') }}"><b>KeyChest</b> Dashboard</a><br/>
                <div style="color:#00a7d7;font-size:18px"><b>Discovers your TLS/HTTPS Certificates</b></div>
            </div>

            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> {{ trans('adminlte_lang::message.someproblems') }}<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="register-box-body">
                <p class="login-box-msg">{{ trans('adminlte_lang::message.registermember') }}</p>

                <a href="{{ url('/login') }}" class="text-center">{{ trans('adminlte_lang::message.membership') }}</a>

                <register-form></register-form>

                @include('adminlte::auth.partials.social_login')

            </div><!-- /.form-box -->
        </div><!-- /.register-box -->
    </div>

    @include('adminlte::layouts.partials.scripts_auth')

    @include('adminlte::auth.terms')
 <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;z-index:-100  ">
      <iframe width="100%" height="100%" src="https://www.youtube.com/embed/Oiju0an1pzQ?controls=0&showinfo=0&rel=0&autoplay=1&loop=0" frameborder="0" allowfullscreen></iframe>
</div>
</body>

@endsection
