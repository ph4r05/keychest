@extends('layouts.landing')

@section('header-title')
    Confirm your KeyChest account to receive notifications
@endsection

@section('content-body')

    @if(!$confirm && $res)
        <div class="alert alert-info">
            <strong><i class="fa fa-question-circle"></i>&nbsp;KeyChest account activation</strong>
        </div>

        <p>
            Please use the link below to <strong>confirm activation of your account </strong> in KeyChest.
            @if($apiKey)
            It also confirms validity of an API key requested by a server at the address <strong>{{ $apiKey->ip_registration }}</strong>
            as received at {{ $apiKey->created_at }}.
            @endif
            <br/>

            <a class="tc-rich-electric-blue" href="{{ url('verifyEmail/' . $token . '/' . $apiKeyToken . '?confirm=1') }}" rel="nofollow"
                    >{{ url('verifyEmail/' . $token . '/' . $apiKeyToken . '?confirm=1') }}</a>
        </p>

        <p>
            <br/><br/>
            If you got here by mistake, you can use the following link to stop receiving KeyChest emails. <br/>
            <a class="tc-rich-electric-blue"  href="{{ url('blockAccount/' . $token . '?confirm=1') }}" rel="nofollow"
                    >{{ url('blockAccount/' . $token . '?confirm=1') }}</a>
        </p>

    @elseif(!empty($res))
        <div class="alert alert-success">
            <strong><i class="fa fa-check-circle"></i>&nbsp;You have activated your account</strong>
        </div>

        <p>
            You can login to your account with a Twitter, Linkedin, GitHub, Facebook, or Google+ account authentication
            so long as it is associated with the same email address.
        </p>

        @if ($res && empty($res->password))
        <p>
            If you prefer using a separate KeyChest account, please set your new password now.
            @include('account.partials.set_password',[
                'token' => $token,
                'user' => $res,
            ])
        </p>
        @endif

    @else
        <div class="alert alert-warning">
            <strong><i class="fa fa-exclamation-circle"></i>&nbsp;We couldn't activate your account</strong>
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

