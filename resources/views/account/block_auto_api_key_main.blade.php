@extends('layouts.landing')

@section('header-title')
    Block unsolicited API key registration
@endsection

@section('content-body')

    @if(!$confirm && $res)
        <div class="alert alert-info">
            <strong><i class="fa fa-question-circle"></i> You are about to block unsolicited API key registration</strong>
        </div>

        <p>
            Are you sure you want to block KeyChest from allowing unsolicited API key registration?
            Then click on the following link:<br/>
            <a href="{{ url('blockAutoApiKeys/' . $token . '/?confirm=1') }}" rel="nofollow"
                    >{{ url('blockAutoApiKeys/' . $token . '/?confirm=1') }}</a>.
        </p>

    @elseif(!empty($res))
        <div class="alert alert-success">
            <strong><i class="fa fa-check-circle"></i> We have successfully completed your request</strong>
        </div>

        <p>
            We have successfully blocked KeyChest from sending you new API registration
            requests to {{ $res->getUser()->email }}.
        </p>

        <p>
            If you change your mind later, you can also change your
            account settings on the <b>Account</b> page of your <a href="{{ url('/login')}}"
            >KeyChest dashboard</a>.
        </p>

    @else
        <div class="alert alert-warning">
            <strong><i class="fa fa-exclamation-circle"></i>We can't complete your request</strong>
        </div>

        <p>
            The request you submitted contained an expired or invalid authorization token.
        </p>

        <p>
            @include('account.partials.problem_footer')
        </p>
    @endif
@endsection

