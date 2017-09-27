@extends('layouts.landing')

@section('header-title')
    Verify email address
@endsection

@section('content-body')

    @if(!$confirm && $res)
        <div class="alert alert-info">
            <strong><i class="fa fa-question-circle"></i> You are about to confirm the email for KeyChest</strong>
        </div>

        <p>
            The KeyChest is asking you to confirm an email address for the KeyChest account and to confirm
            a new API key requested from the <strong>{{ $apiKey->ip_registration }}</strong>
            at {{ $apiKey->created_at }}. <br/>

            If you want to confirm please visit the following link:
            <a href="{{ url('verifyEmail/' . $user->email_verify_token . '/' . $apiKey->api_verify_token . '?confirm=1') }}" rel="nofollow"
                    >{{ url('verifyEmail/' . $user->email_verify_token . '/' . $apiKey->api_verify_token . '?confirm=1') }}</a>.<br/>
        </p>

        <p>
            If the action was not initiated by you, you can decide to block this request by visiting the following link:
            <a href="{{ url('blockAccount/' . $user->email_verify_token . '?confirm=1') }}" rel="nofollow"
                    >{{ url('blockAccount/' . $user->email_verify_token . '?confirm=1') }}</a>. <br/>
            In that case KeyChest won't send you any more email on this address.
        </p>

    @elseif(!empty($res))
        <div class="alert alert-success">
            <strong><i class="fa fa-check-circle"></i> We have successfully completed your request</strong>
        </div>

        <p>
            You have successfully verified your email for KeyChest account.
        </p>

        <p>
            As you are here, you can set the password for your KeyChest account
            @include('account.partials.set_password',[
                'token' => $user->remember_token,
                'user' => $user,
            ])
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

