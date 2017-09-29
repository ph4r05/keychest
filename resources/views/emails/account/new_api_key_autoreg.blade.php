@extends('emails.base')

@section('content')
    <p>Hi,</p>

    <p>
        We've received a request to register a new API key from the IP address <strong>{{ $apiKey->ip_registration }}</strong>
        at {{ $apiKey->created_at }} Greenwich time (GMT).
    </p>
    <p>
        This request is typically a result of an automation, which uses KeyChest's API. A confirmed API key has
        a basic API access to your KeyChest account (e.g., add a new server, request a spot check). Details of the API
        are available at <a class="tc-rich-electric-blue" href="https://api.enigmabridge.com/api/#keychest">https://api.enigmabridge.com</a>.
    </p>

    <p>
        Please use this link below to confirm and instantly enable the API key.<br/>
        <a href="{{ url('confirmApiKey/' . $apiToken) }}" rel="nofollow"
                >{{ url('confirmApiKey/' . $apiToken) }}</a>
    </p>

    <p>
        If you don't recognize this request, you can disable this API key here.<br/>
        <a href="{{ url('revokeApiKey/' . $apiToken) }}" rel="nofollow"
                >{{ url('revokeApiKey/' . $apiToken) }}</a><br/>
    </p>

    <p>
        You can also block all future unauthenticated API key registrations your email address with the following link.<br/>
        <a href="{{ url('blockAutoApiKeys/' . $blockApiToken) }}" rel="nofollow"
                >{{ url('blockAutoApiKeys/' . $blockApiToken) }}</a><br/><br/>

        You can change these settings at any time from the
        <a href="{{ url('home/license') }}">Dashboard of your KeyChest account</a>.
    </p>

    <p>
        Kind regards, <br/>
          <i>{{ config('app.name') }} &amp; Enigma Bridge</i>
    </p>

@endsection
