@extends('emails.base')

@section('content')
    <p>Hi,</p>

    <p>
        Keychest has got an user registration request for this email from the IP <strong>{{ $apiKey->ip_registration }}</strong>
        at {{ $apiKey->created_at }}. <br/>

        You can confirm account creation and allow the client to access the KeyChest account being created on
        the following link:
        <a href="{{ url('verifyEmail/' . $user->email_verify_token . '/' . $apiKey->api_verify_token) }}"
                >{{ url('verifyEmail/' . $user->email_verify_token . '/' . $apiKey->api_verify_token) }}</a>.<br/>

        If the action was not initiated by you, you can decide to block this request by visiting the following link:
        <a href="{{ url('blockAccount/' . $user->email_verify_token) }}"
                >{{ url('blockAccount/' . $user->email_verify_token) }}</a>. <br/>
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
