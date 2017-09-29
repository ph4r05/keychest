@extends('layouts.landing')

@section('header-title')
    Confirm API key
@endsection

@section('content-body')

    @if(!$confirm && $res)
        <div class="alert alert-info">
            <strong><i class="fa fa-alert-move fa-question-circle"></i>Enabling KeyChest API Key</strong>
        </div>

        <p>
            KeyChest requires use of a valid API key for authenticated functions of its RESTful API. The API documentation
            is available at <a class="tc-rich-electric-blue" href="https://api.enigmabridge.com/api/#keychest">https://api.enigmabridge.com</a>.
        </p>
        <p>
            Please follow the link below to <strong>enable</strong> the {{ $res->api_key }} API key. <br/>
            <a class="tc-rich-electric-blue" href="{{ url('confirmApiKey/' . $apiKeyToken . '/?confirm=1') }}" rel="nofollow"
                    >{{ url('confirmApiKey/' . $apiKeyToken . '/?confirm=1') }}</a>
        </p>

        <p>
            However, if you want to <strong>disable</strong> the {{ $res->api_key }} API key instead, use this link.<br/>
            <a class="tc-rich-electric-blue" href="{{ url('revokeApiKey/' . $apiKeyToken . '/?confirm=1') }}" rel="nofollow"
                    >{{ url('revokeApiKey/' . $apiKeyToken . '/?confirm=1') }}</a>
        </p>

    @elseif(!empty($res))
        <div class="alert alert-success">
            <strong><i class="fa fa-alert-move fa-check-circle"></i>We have successfully completed your request</strong>
        </div>

        <p>
            We have successfully confirmed the API key <em>{{ $apiKey->api_key }}</em> for use.
        </p>

        <p>
            If you change your mind later, you can also change your
            account settings on the <b>Account</b> page of your <a class="tc-rich-electric-blue" href="{{ url('/login')}}"
            >KeyChest dashboard</a>.
        </p>

    @else
        <div class="alert alert-warning">
            <strong><i class="fa fa-alert-move fa-exclamation-circle"></i>We can't complete your request</strong>
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

