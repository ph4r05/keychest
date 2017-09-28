@extends('emails.base')

@section('content')
    <p>Hi,</p>

    <p>
        We've got a new request for an API key registration from the IP <strong>{{ $apiKey->ip_registration }}</strong>
        at {{ $apiKey->created_at }}.
    </p>

    <p>
        If you confirm this request please visit the following link:<br/>
        <a href="{{ url('confirmApiKey/' . $apiToken) }}" rel="nofollow"
                >{{ url('confirmApiKey/' . $apiToken) }}</a>.
    </p>

    <p>
        You can also revoke this request if you deem the registration suspicious on the following link:<br/>
        <a href="{{ url('revokeApiKey/' . $apiToken) }}" rel="nofollow"
                >{{ url('revokeApiKey/' . $apiToken) }}</a>.<br/>

        The confirmed API key would have a basic access to your KeyChest account (e.g., add a new server, do a spot check).
    </p>

    <p>
        You can also decide to block further unsolicited API key registrations for your account on the following link:<br/>
        <a href="{{ url('blockAutoApiKeys/' . $blockApiToken) }}" rel="nofollow"
                >{{ url('blockAutoApiKeys/' . $blockApiToken) }}</a>.<br/><br/>

        If you change your mind later, you can adjust this and other account options in your
        <a href="{{ url('home/license') }}">KeyChest dashboard</a>.
    </p>

    <p>
        Kind regards <br/>
          <i>{{ config('app.name') }} &amp; Enigma Bridge</i>
    </p>

@endsection
