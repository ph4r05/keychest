@extends('emails.base')

@section('content')
    <p>Hi,</p>

    <p>
        At the moment, KeyChest monitors:
        {{ $md->getNumActiveWatches() }} {{ trans_choice('emails.server', $md->getNumActiveWatches()) }},
        {{ $md->getNumCertsActive() }} {{ trans_choice('emails.certificate', $md->getNumCertsActive()) }}.
    </p>

    <p>
    @if ($md->getCertExpire7days()->isNotEmpty())

        {{ trans_choice('emails.expiry7', $md->getCertExpire7days()->count(), [
            'certificates' => $md->getCertExpire7days()->count()
        ]) }} <br/><br/>

        @component('emails.partials.domain_detail', ['certs' => $md->getCertExpire7days()])
        @endcomponent

    @else
        @lang('emails.expiry7empty')
    @endif
    </p>

    <p>
    @if ($md->getCertExpire28days()->isNotEmpty())
        {{ trans_choice('emails.expiry28', $md->getCertExpire28days()->count(), [
            'certificates' => $md->getCertExpire28days()->count()
        ]) }} <br/><br/>

        @component('emails.partials.domain_detail', ['certs' => $md->getCertExpire28days()])
        @endcomponent

    @else
        @lang('emails.expiry28empty')
    @endif
    </p>

    {{--”“--}}
    {{--We have also detected XXX incidents related to HTTPS/TLS configuration on your servers.--}}

    <p>
        You can see more details about incidents when you login to your dashboard at:
        <a href="{{ url('/login') }}">{{ url('/login') }}</a>
    </p>

    <p>
        @component('emails.partials.unsubscribe')
        @endcomponent
    </p>

    <p>
    Kind regards <br/>
          <i>{{ config('app.name') }} &amp; Enigma Bridge</i>
    </p>

    @component('emails.partials.news_footer', ['news'=>$news])
    @endcomponent
@endsection
