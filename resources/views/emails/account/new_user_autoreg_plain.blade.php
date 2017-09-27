Hi,

Keychest has got an user registration request for this email from the IP {{ $apiKey->ip_registration }}
at {{ $apiKey->created_at }}.

You can confirm account creation and allow the client to access the KeyChest account being created on
the following link:
{{ url('verifyEmail/' . $user->email_verify_token . '/' . $apiKey->api_verify_token) }}

If the action was not initiated by you, you can decide to block this request by visiting the following link:
{{ url('blockAccount/' . $user->email_verify_token) }}

In that case KeyChest won't send you any more email on this address.

If you change your mind later, you can re-activate the account by logging in via
KeyChest Login page {{ url('login') }}.

In case you've lost your password you can ask for a password reset: {{ url('password/reset') }}.

Kind regards
  {{ config('app.name') }} & Enigma Bridge
