@extends('layouts.landing')

@section('header-title')
    Block KeyChest from using the account
@endsection

@section('content-body')

    @if(!$confirm && $res)
        <div class="alert alert-info">
            <strong><i class="fa fa-question-circle"></i> Block emails from KeyChest</strong>
        </div>

        <p>
            Use the following link to block use of your email address by KeyChest. It will also disable use of all
            KeyChest API calls with your email address.<br/>
            <a class="tc-rich-electric-blue" href="{{ url('blockAccount/' . $token . '/?confirm=1') }}" rel="nofollow"
                    >{{ url('blockAccount/' . $token . '/?confirm=1') }}</a>.
        </p>

    @elseif(!empty($res))
        <div class="alert alert-success">
            <strong><i class="fa fa-check-circle"></i>KeyChest will stopped your email address</strong>
        </div>

        <p>
            We have marked your email {{ $res->email }} as restricted and KeyChest will not use it for any communication.
        </p>

        <p>
            You can re-activate this email address by logging to KeyChest via
            <a class="tc-rich-electric-blue" href="{{ url('login') }}">KeyChest Login</a> page. This page also provides
            a <a class="tc-rich-electric-blue" href="{{ url('password/reset') }}">password reset</a> facility.
        </p>

    @else
        <div class="alert alert-warning">
            <strong><i class="fa fa-exclamation-circle"></i>We can't update your email settings</strong>
        </div>

        <p>
            The link you used probably contained an expired or invalid authorization token. Please get in touch with us
            at <a href="mailto:support@enigmabridge.com">support@enigmabridge.com</a> and we will assist you shortly.
        </p>

        <p>
            @include('account.partials.problem_footer')
        </p>
    @endif
@endsection

