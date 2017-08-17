Hi,

@if ($md->getCertExpire7days()->isNotEmpty())

    {{ trans_choice('emails.expiry7', $md->getCertExpire7days()->count(), [
        'certificates' => $md->getCertExpire7days()->count()
    ]) }}

    @component('emails.partials.domain_detail_plain', ['certs' => $md->getCertExpire7days()])
    @endcomponent
@else
    @lang('emails.expiry7empty')
@endif

@if ($md->getCertExpire28days()->isNotEmpty())
    {{ trans_choice('emails.expiry28', $md->getCertExpire28days()->count(), [
        'certificates' => $md->getCertExpire28days()->count()
    ]) }}

    @component('emails.partials.domain_detail_plain', ['certs' => $md->getCertExpire28days()])
    @endcomponent

@else
    @lang('emails.expiry28empty')
@endif

{{--”“--}}
{{--We have also detected XXX incidents related to HTTPS/TLS configuration on your servers.--}}


At the moment, KeyChest monitors: {{ $md->getNumActiveWatches() }} {{ trans_choice('emails.server', $md->getNumActiveWatches()) }}, {{ $md->getNumCertsActive() }} {{ trans_choice('emails.certificate', $md->getNumCertsActive()) }}.


You can see more details about incidents when you login to your dashboard at:
{{ url('/login') }}

@component('emails.partials.unsubscribe')
@endcomponent

Kind regards
  {{ config('app.name') }} & Enigma Bridge

@component('emails.partials.news_footer_plain', ['news'=>$news])
@endcomponent

