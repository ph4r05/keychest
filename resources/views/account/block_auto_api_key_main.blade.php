@extends('layouts.landing')

@section('header-title')
    Block unsolicited API key registration
@endsection

@section('content-body')

    @if(!$confirm && $res)
        <div class="alert alert-info">
            <strong><i class="fa fa-alert-move fa-question-circle"></i>Block anonymous API key registrations</strong>
        </div>

        <p>
            KeyChest offers several API functions without any authentication to simplify enrolment and monitoring
            of new servers with automated tools. You can disable this functionality to strengthen your control of
            the monitoring.
        </p>
        <p>
            Details of the API documentation are available at
            <a class="tc-rich-electric-blue" href="https://api.enigmabridge.com/api/#keychest">https://api.enigmabridge.com</a>.
        </p>


        <p>

            Please use the following link to disable unauthenticated calls of the KeyChest API. Each new registration
            will then require a valid API key.<br/>
            <a class="tc-rich-electric-blue" href="{{ url('blockAutoApiKeys/' . $token . '/?confirm=1') }}" rel="nofollow"
                    >{{ url('blockAutoApiKeys/' . $token . '/?confirm=1') }}</a>
        </p>

    @elseif(!empty($res))
        <div class="alert alert-success">
            <strong><i class="fa fa-alert-move fa-check-circle"></i>We have updated your KeyChest API access</strong>
        </div>

        <p>
            We have disabled unauthenticated use of the KeyChest API with your email address {{ $res->email }}.
        </p>

        <p>
            You can use your <a class="tc-rich-electric-blue" href="{{ url('/login')}}">KeyChest Account page</a>
            to revert this change at any time.
        </p>

    @else
        <div class="alert alert-warning">
            <strong><i class="fa fa-alert-move fa-exclamation-circle"></i>We can't change API access</strong>
        </div>

        <p>
            The link you used probably contained an expired or invalid authorization token. Please get in touch with us
            at <a href="mailto:support@enigmabridge.com">support@enigmabridge.com</a> and we will assist you shortly.
        </p>

        <p>
            @include('account.partials.problem_footer')
        </p>
    @endif
@endsection

