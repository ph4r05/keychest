@extends('layouts.landing')

@section('header-title')
    Revoke KeyChest API key
@endsection

@section('content-body')

    @if(!$confirm && $res)
        <div class="alert alert-info">
            <strong><i class="fa fa-question-circle"></i>&nbsp;Disabling KeyChest API Key</strong>
        </div>

        KeyChest requires use of a valid API key for authenticated functions of its RESTful API. The API documentation
        is available at <a class="tc-rich-electric-blue" href="https://api.enigmabridge.com/api/#keychest">https://api.enigmabridge.com</a>.

        <p>
            Please follow the link below to <strong>disable</strong> the {{ $res->api_key }} API key. <br/>
            <a class="tc-rich-electric-blue" href="{{ url('revokeApiKey/' . $apiKeyToken . '/?confirm=1') }}" rel="nofollow"
                    >{{ url('revokeApiKey/' . $apiKeyToken . '/?confirm=1') }}</a>
        </p>

        <p>
            However, if you want to <strong>enable</strong> the {{ $res->api_key }} API key instead, use this link.<br/>
            <a class="tc-rich-electric-blue" href="{{ url('confirmApiKey/' . $apiKeyToken . '/?confirm=1') }}" rel="nofollow"
            >{{ url('confirmApiKey/' . $apiKeyToken . '/?confirm=1') }}</a>
        </p>


    @elseif(!empty($res))
        <div class="alert alert-success">
            <strong><i class="fa fa-check-circle"></i>&nbsp;KeyChest API key is disabled</strong>
        </div>

        <p>
            Your KeyChest API key <em>{{ $apiKey->api_key }}</em> has been revoked and can't be used any more.
        </p>

    @else
        <div class="alert alert-warning">
            <strong><i class="fa fa-exclamation-circle"></i>&nbsp;We can't complete your API key validation request</strong>
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

