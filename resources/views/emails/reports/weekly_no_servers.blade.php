@extends('emails.base')

@section('content')
    <p>Hi,</p>

    <p>
        Please log in to your dashboard and add some of your servers or registered domains to enjoy
        the power and simplicity of KeyChest.

        You will have to visit <a href="{{ url('/home') }}">{{ url('/home') }}</a> and use the recipient
        address of this email.
    </p>

    <p>
        @component('emails.partials.unsubscribe', ['user' => $md->user])
        @endcomponent
    </p>

    <p>
    Kind regards <br/>
          <i>{{ config('app.name') }} &amp; Enigma Bridge</i>
    </p>

    @component('emails.partials.news_footer', ['news' => $news])
    @endcomponent
@endsection
