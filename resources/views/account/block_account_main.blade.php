@extends('layouts.landing')

@section('header-title')
    Block KeyChest from using the account
@endsection

@section('content-body')

    @if(!$confirm && $res)
        <div class="alert alert-info">
            <strong><i class="fa fa-question-circle"></i> You are about to block your KeyChest account</strong>
        </div>

        <p>
            Are you sure you want to block your KeyChest account and disable KeyChest using your email?
            Then click on the following link:<br/>
            <a href="{{ url('blockAccount/' . $token . '/?confirm=1') }}" rel="nofollow"
                    >{{ url('blockAccount/' . $token . '/?confirm=1') }}</a>.
        </p>

    @elseif(!empty($res))
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

