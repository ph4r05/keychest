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
            The KeyChest is asking you to <strong>confirm an email address</strong> for the KeyChest account
            @if($apiKey)
            and to confirm a new API key requested from the <strong>{{ $apiKey->ip_registration }}</strong>
            at {{ $apiKey->created_at }}.
            @endif
            <br/>

            If you want to confirm please visit the following link: <br/>
            <a href="{{ url('verifyEmail/' . $res->email_verify_token . '/' . $apiKeyToken . '?confirm=1') }}" rel="nofollow"
                    >{{ url('verifyEmail/' . $res->email_verify_token . '/' . $apiKeyToken . '?confirm=1') }}</a>.
        </p>

        <p>
            If the action was not initiated by you, you can decide to <strong>block this request</strong>
            by visiting the following link: <br/>
            <a href="{{ url('blockAccount/' . $res->email_verify_token . '?confirm=1') }}" rel="nofollow"
                    >{{ url('blockAccount/' . $res->email_verify_token . '?confirm=1') }}</a>.
        </p>

        <p>
            In that case KeyChest won't send you any more email on this address.
        </p>

    @elseif(!empty($res))
        <div class="alert alert-success">
            <strong><i class="fa fa-check-circle"></i> We have successfully completed your request</strong>
        </div>

        <p>
            You have successfully verified your email for KeyChest account.
        </p>

        @if ($res && empty($res->password))
        <p>
            As you are here, you can set the password for your KeyChest account
            @include('account.partials.set_password',[
                'token' => $token,
                'user' => $res,
            ])
        </p>
        @endif

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

