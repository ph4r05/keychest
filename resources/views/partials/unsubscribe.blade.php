@if(!$confirm && $user)
    <div class="alert alert-primary">
        <strong><i class="fa fa-question-circle"></i> You are about to cancel weekly reports</strong>
    </div>

    <p>
        Are you sure you want to cancel weekly reports subscriptions? Click on the following link to cancel:
        <a href="{{ url('unsubscribe/' . $token . '/?confirm=1') }}" rel="nofollow"
                >{{ url('unsubscribe/' . $token . '/?confirm=1') }}</a>
    </p>

@elseif($res && $res->isTokenFound())
    <div class="alert alert-success">
        <strong><i class="fa fa-check-circle"></i> We have successfully completed your request</strong>
    </div>

    <p>
        We have successfully disabled weekly email updates to your address {{ $res->getUser()->email }}.

    </p>
    <p>
        You can change this and other options on the <b>Account</b> page of your <a href="{{ url('/login')}}"> KeyChest
            dashboard</a>.
    </p>

@else
    <div class="alert alert-warning">
        <strong><i class="fa fa-exclamation-circle"></i>We can't complete your request</strong>
    </div>

    <p>
        The request you submitted contained an expired or invalid authorization token.
    </p>

    <p>
        If you used a correct link, please let us know via email
        at <i>support@enigmabridge.com</i> and we will get back to you shortly. You can also change your
        account settings on the <b>Account</b> page of your <a href="{{ url('/login')}}"> KeyChest
            dashboard</a>.
    </p>
@endif
