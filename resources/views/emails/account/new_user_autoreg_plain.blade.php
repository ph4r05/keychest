Hi,


We have created a new KeyChest account for you to provide HTTPS/TLS monitoring for a server with the IP address
of {{ $apiKey->ip_registration }}.
We received the initial request at {{ $apiKey->created_at }} Greenwich time (GMT).<br/>


This request is typically a result of an automation, which uses KeyChest's API. Details of the API
are available at https://api.enigmabridge.com/api/#keychest.

Please follow the link below to confirm the new account and to set your password.
{{ url('verifyEmail/' . $emailVerifyToken . '/' . $apiToken) }}

If you are not aware of this monitoring request, please use the alternative link below.
{{ url('blockAccount/' . $blockAccountToken) }}


If you decide to use KeyChest monitoring at a later date, you can re-create your account from the
KeyChest Login page {{ url('login') }}.
This page also provides a password reset facility: {{ url('password/reset') }}.

Kind regards,
  {{ config('app.name') }} & Enigma Bridge
