@extends('emails.base')

@section('content')
    <p>Hi,</p>

    <p>
        We have created a new KeyChest account for you to provide HTTPS/TLS monitoring for a server with the IP address
        of <strong>{{ $apiKey->ip_registration }}</strong>. We received the initial request at {{ $apiKey->created_at }}
                Greenwich time (GMT).<br/>
    </p>

    <p>
        This request is typically a result of an automation, which uses KeyChest's API. Details of the API
        are available at <a class="tc-rich-electric-blue" href="https://api.enigmabridge.com/api/#keychest">https://api.enigmabridge.com</a>.
    </p>


    <p>
        Please follow the link below to confirm the new account and to set your password. <br/>
        <a href="{{ url('verifyEmail/' . $emailVerifyToken . '/' . $apiToken) }}" rel="nofollow"
                >{{ url('verifyEmail/' . $emailVerifyToken . '/' . $apiToken) }}</a>
    </p>

    <p>
        If you are not aware of this monitoring request, please use the alternative link below.<br/>
        <a href="{{ url('blockAccount/' . $blockAccountToken) }}" rel="nofollow"
                >{{ url('blockAccount/' . $blockAccountToken) }}</a> <br/> <br/>
    </p>

    <p>
        If you decide to use KeyChest monitoring at a later date, you can re-create your account at the
        <a href="{{ url('login') }}">KeyChest Login</a> page. This page also provides a
        <a href="{{ url('password/reset') }}">password reset facility</a>.
    </p>

    <p>
        Kind regards, <br/>
          <i>{{ config('app.name') }} &amp; Enigma Bridge</i>
    </p>

@endsection
