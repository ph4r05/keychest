@extends('emails.base')

@section('content')
    <p>Hi,</p>

    <p>
    @if ($md->getCertExpire7days()->isNotEmpty())

    {{ trans_choice('emails.expiry7', $md->getCertExpire7days()->count(), [
        'certificates' => $md->getCertExpire7days()->count()
    ]) }}

    {{--<domain name> <date>--}}

    @else
        @lang('emails.expiry7empty')
    @endif
    </p>

    <p>
    @if ($md->getCertExpire28days()->isNotEmpty())
        {{ trans_choice('emails.expiry28', $md->getCertExpire28days()->count(), [
            'certificates' => $md->getCertExpire28days()->count()
        ]) }}

    @else
        @lang('emails.expiry28empty')
    @endif
    </p>

    {{--”“--}}
    {{--We have also detected XXX incidents related to HTTPS/TLS configuration on your servers.--}}

    <p>
        At the moment, KeyChest monitors:
        {{ $md->getActiveWatches()->count() }} {{ trans_choice('emails.server', $md->getActiveWatches()->count()) }},
        {{ $md->getTlsCertsIds()->count() }} {{ trans_choice('emails.certificate', $md->getTlsCertsIds()->count()) }}.
    </p>

    <p>
        You can see more details about incidents when you login to your dashboard at:
        <a href="{{ url('/register') }}">{{ url('/register') }}</a>
    </p>


    <p>
    Kind regards <br/>
          <i>{{ config('app.name') }} &amp; Enigma Bridge</i>
    </p>
@endsection
