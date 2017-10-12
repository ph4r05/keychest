@component('mail::message')
# ROCA Key Vulnerability Report

We have completed a test of an email signing key with the following result.

@component('mail::panel', ['type' => $allSafe ? 'success' : 'danger'])
@if($allSafe)
The key is secure
@else
We identified some issues with your keys.
@endif
@endcomponent

@if(!empty($pgpKeys))
# PGP Overview
@foreach ($pgpKeys as $key)
## Key 0x{{ $key->keyId }}
@component('mail::table')
| Attribute     | Value   |
| ------------  | ------- |
| Key ID        | {{ $key->keyId }} |
| Master key ID | {{ $key->masterKeyId }} |
| Created at    | {{ $key->createdAt ? $key->createdAt->toFormattedDateString() : '' }} |
| Bit Size      | {{ $key->bitSize }} |
| Test result   | *{{ $key->verdict }}* |

@endcomponent
@endforeach
@endif


@if(!empty($smimeKeys))
## Details of the key
@foreach ($smimeKeys as $key)
@component('mail::table')
| Attribute     | Value   |
| ------------  | ------- |
| Subject       | {{ $key->subject }} |
| Created at    | {{ $key->createdAt ? $key->createdAt->toFormattedDateString() : '' }} |
| Bit Size      | {{ $key->bitSize  }} |
| Test result   | *{{ $key->status != 'ok' ?  'Processing error' : ($key->marked ? 'Vulnerable' : 'Safe') }}* |

@endcomponent
@endforeach
@endif

Thank you for using our KeyChest key check service. The check was performed {{ $checkTime->toFormattedDateString() }}.

@component('mail::button', ['url' => route('tester')])
More information
@endcomponent

Kind Regards,<br>
{{ config('app.name') }} & Enigma Bridge
@endcomponent
