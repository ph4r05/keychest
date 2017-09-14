@if($res->isTokenFound())
    <div class="alert alert-success">
        <strong><i class="fa fa-check-circle"></i> Unsubscribe successful</strong>
    </div>

    <p>
        Email address {{ $res->getUser()->email }} won't receive any more email reports.
    </p>

    <p>
        To manage more KeyChest settings go to the <a href="{{ url('home/license') }}">account settings</a> in KeyChest.
    </p>

@else
    <div class="alert alert-warning">
        <strong><i class="fa fa-exclamation-circle"></i> Token not found</strong>
    </div>

    <p>
        Unfortunately the given token was not found. It may expired or is invalid.
    </p>

    <p>
        In order to unsubscribe, please send an email from your email address at <i>keychest AT enigmabridge.com</i>
        with "UNSUNBSCRIBE" as a subject or alternatively <a href="{{ url('/login') }}">login</a> to the KeyChest
        and unsubscribe in your <a href="{{ url('home/license') }}">account settings</a>.
    </p>
@endif
