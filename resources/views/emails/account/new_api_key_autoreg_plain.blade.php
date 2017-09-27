Hi,

We've got a new request for an API key registration from the IP {{ $apiKey->ip_registration }}
at {{ $apiKey->created_at }}.

If you confirm this request please visit the following link:
{{ url('confirmApiKey/' . $apiKey->api_verify_token) }}

You can also revoke this request if you deem the registration suspicious on the following link:
{{ url('revokeApiKey/' . $apiKey->api_verify_token) }}

The confirmed API key would have a basic access to your KeyChest account (e.g., add a new server, do a spot check).


You can also decide to block further unsolicited API key registrations for your account on the following link:
{{ url('blockAutoApiKeys/' . $user->email_verify_token) }}

If you change your mind later, you can adjust this and other account options in your
 KeyChest dashboard: {{ url('home/license') }}.

Kind regards
  {{ config('app.name') }} & Enigma Bridge
