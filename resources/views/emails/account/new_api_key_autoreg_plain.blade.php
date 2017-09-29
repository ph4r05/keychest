Hi,

We've received a request to register a new API key from the IP address {{ $apiKey->ip_registration }}
at {{ $apiKey->created_at }} Greenwich time (GMT).

This request is typically a result of an automation, which uses KeyChest's API. A confirmed API key has
a basic API access to your KeyChest account (e.g., add a new server, request a spot check). Details of the API
are available at https://api.enigmabridge.com/api/#keychest.




Please use this link below to confirm and instantly enable the API key.
{{ url('confirmApiKey/' . $apiToken) }}

If you don't recognize this request, you can disable this API key here.
{{ url('revokeApiKey/' . $apiToken) }}

The confirmed API key would have a basic access to your KeyChest account (e.g., add a new server, do a spot check).


You can also block all future unauthenticated API key registrations your email address with the following link.
{{ url('blockAutoApiKeys/' . $blockApiToken) }}

You can change these settings at any time from the Dashboard of your KeyChest account at {{ url('home/license') }}.

Kind regards,
  {{ config('app.name') }} & Enigma Bridge
