Hi,

@if ($md->getCertExpire7days()->isNotEmpty())

    {{ trans_choice('emails.expiry7', $md->getCertExpire7days()->count(), [
        'certificates' => $md->getCertExpire7days()->count()
    ]) }}

    {{--<domain name> <date>--}}
@else
    @lang('emails.expiry7empty')
@endif

@if ($md->getCertExpire28days()->isNotEmpty())
    {{ trans_choice('emails.expiry28', $md->getCertExpire28days()->count(), [
        'certificates' => $md->getCertExpire28days()->count()
    ]) }}

@else
    @lang('emails.expiry28empty')
@endif

{{--”“--}}
{{--We have also detected XXX incidents related to HTTPS/TLS configuration on your servers.--}}


At the moment, KeyChest monitors: {{ $md->getActiveWatches()->count() }} {{ trans_choice('emails.server', $md->getActiveWatches()->count()) }}, {{ $md->getTlsCertsIds()->count() }} {{ trans_choice('emails.certificate', $md->getTlsCertsIds()->count()) }}.


You can see more details about incidents when you login to your dashboard at:
{{ url('/register') }}

Kind regards
  {{ config('app.name') }} & Enigma Bridge


