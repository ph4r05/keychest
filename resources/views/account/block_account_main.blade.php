@extends('layouts.landing')

@section('header-title')
    Block KeyChest from using the account
@endsection

@section('content-body')
    @if(!empty($res))
        <div class="alert alert-success">
            <strong><i class="fa fa-check-circle"></i> We have successfully completed your request</strong>
        </div>

        <p>
            We have successfully blocked KeyChest from using your address {{ $res->getUser()->email }}.
        </p>

        <p>
            If you change your mind later, you can re-activate the account by logging in via
            <a href="{{ url('login') }}">KeyChest Login</a> page. In case you've lost your password
            you can ask for a <a href="{{ url('password/reset') }}">password reset</a>.
        </p>

    @else
        <div class="alert alert-warning">
            <strong><i class="fa fa-exclamation-circle"></i>We can't complete your request</strong>
        </div>

        <p>
            The request you submitted contained an expired or invalid authorization token.
        </p>

        <p>
            @include('account.partials.problem_footer')
        </p>
    @endif
@endsection

