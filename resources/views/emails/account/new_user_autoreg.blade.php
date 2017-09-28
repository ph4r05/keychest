@extends('emails.base')

@section('content')
    <p>Hi,</p>

    <p>
        Keychest has got an user registration request for this email from the IP <strong>{{ $apiKey->ip_registration }}</strong>
        at {{ $apiKey->created_at }}. <br/>
    </p>

    <p>
        You can confirm account creation and allow the client to access the KeyChest account being created on
        the following link: <br/>
        <a href="{{ url('verifyEmail/' . $emailVerifyToken . '/' . $apiToken) }}" rel="nofollow"
                >{{ url('verifyEmail/' . $emailVerifyToken . '/' . $apiToken) }}</a>.
    </p>

    <p>
        If the action was not initiated by you, you can decide to block this request by visiting the following link:<br/>
        <a href="{{ url('blockAccount/' . $blockAccountToken) }}" rel="nofollow"
                >{{ url('blockAccount/' . $blockAccountToken) }}</a>. <br/> <br/>
        In that case KeyChest won't send you any more email on this address.
    </p>

    <p>
        If you change your mind later, you can re-activate the account by logging in via
        <a href="{{ url('login') }}">KeyChest Login</a> page. In case you've lost your password
        you can ask for a <a href="{{ url('password/reset') }}">password reset</a>.
    </p>

    <p>
        Kind regards <br/>
          <i>{{ config('app.name') }} &amp; Enigma Bridge</i>
    </p>

@endsection
