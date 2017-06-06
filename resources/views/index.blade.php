@extends('layouts.app')

@section('content-nav')
    {{--<li><a onclick="scrollToTarget('#learn')">Learn more</a></li>--}}
@endsection

@section('content')
    <!-- loading placeholder -->
    <div class="bloc bloc-fill-screen tc-onyx bgc-white l-bloc" id="intro-placeholder" style="height: 200px;">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <img src="/images/logo2-rgb_keychest.png" class="img-responsive center-block" width="300">
                    <h3 class="text-center mg-lg hero-bloc-text-sub  tc-rich-electric-blue">
                        Track and plan for 100% HTTPS uptime
                    </h3>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-8 col-sm-offset-2">
                    <div class="panel panel-default">
                        <div class="panel-body alert-waiting">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vue search component -->
    <quicksearch></quicksearch>

    <!-- learn -->
    <div class="bloc tc-onyx bgc-isabelline" id="learn">
        <div class="container bloc-sm">
            <div class="row">
                <div class="col-sm-12">
                    <h1 class="mg-md tc-rich-electric-blue">
                        Specification for accounts
                    </h1>
                    <p>
                        While it’s handy to quickly check the status of a particular domain, what we really want is to get weekly emails with the status of all our domains.&nbsp;We also want to test, whether certificates have actually been applied to web servers and services. Sometimes, we get new certificates but forget to either copy them to the right location, or restart the service, which uses them.&nbsp; <br><br>This can only be done with some kind of account management. We have an initial idea of what kind of information we need to link to such accounts - see below. But we also want to make it really useful for you. Please let us know what kind of information you’d like to see. We will keep updating the columns bellow with your suggestions.<br>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <!-- learn END -->

    <!-- bloc-2 -->
    <div class="bloc tc-onyx bgc-isabelline" id="bloc-2">
        <div class="container bloc-sm">
            <div class="row equal">
                <div class="col-sm-4">
                    <div class="panel panel-fullwidth">
                        <div class="panel-heading">
                            <h2 class="mg-clear text-center tc-rich-electric-blue">
                                Editing
                            </h2>
                            <p class="mg-clear text-center tc-rich-electric-blue">(things you can change)</p>
                        </div>
                        <div class="panel-body">
                            <ul class="list-unstyled list-sp-md">
                                <li>
                                    <h4 class="text-center mg-md tc-onyx">
                                        Choose day/hour for weekly emails
                                    </h4>
                                </li>
                                <li>
                                    <h4 class="text-center mg-md tc-onyx">
                                        List of domains to monitor
                                    </h4>
                                </li>
                                <li>
                                    <h4 class="text-center mg-md tc-onyx">
                                        YES/NO - to monitor subdomains
                                    </h4>
                                </li>
                                <li>
                                    <h4 class="text-center mg-md tc-onyx">
                                        Server ports to check effective certs
                                    </h4>
                                </li>
                                <li>
                                    <h4 class="mg-md tc-onyx text-center">
                                        Set timezone
                                    </h4>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="panel panel-fullwidth">
                        <div class="panel-heading">
                            <h2 class="mg-clear text-center tc-rich-electric-blue">
                                Viewing
                            </h2>
                            <p class="mg-clear text-center tc-rich-electric-blue">(things we show you)</p>
                        </div>
                        <div class="panel-body">
                            <ul class="list-unstyled list-sp-md">
                                <li>
                                    <h4 class="text-center mg-md tc-onyx">
                                        Expired certificates - last 3 months
                                    </h4>
                                </li>
                                <li>
                                    <h4 class="text-center mg-md tc-onyx">
                                        Expiring in 7 days
                                    </h4>
                                </li>
                                <li>
                                    <h4 class="text-center mg-md tc-onyx">
                                        Expiring in next 28 days
                                    </h4>
                                </li>
                                <li>
                                    <h4 class="text-center mg-md tc-onyx">
                                        Expiring in next 3 months
                                    </h4>
                                </li>
                                <li>
                                    <h4 class="mg-md text-center tc-onyx">
                                        Data udpated within 24 hours
                                    </h4>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="panel panel-fullwidth">
                        <div class="panel-heading">
                            <h2 class="mg-clear text-center tc-rich-electric-blue">
                                Emailing (KPI/planner)
                            </h2>
                            <p class="mg-clear text-center tc-rich-electric-blue">(things for emails)</p>
                        </div>
                        <div class="panel-body">
                            <ul class="list-unstyled list-sp-md">
                                <li>
                                    <h4 class="text-center mg-md tc-onyx">
                                        Total number of certificates
                                    </h4>
                                </li>
                                <li>
                                    <h4 class="text-center mg-md tc-onyx">
                                        Total number of domains
                                    </h4>
                                </li>
                                <li>
                                    <h4 class="text-center mg-md tc-onyx">
                                        Critical - expiring in the next 7 days
                                    </h4>
                                </li>
                                <li>
                                    <h4 class="text-center mg-md tc-onyx">
                                        Renewed in last 28 days
                                    </h4>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- bloc-2 END -->

    <!-- bloc-3 -->
    <div class="bloc tc-onyx bgc-isabelline " id="bloc-3">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="row">
                        <div class="col-sm-12">
                            <form id="form_6" novalidate="" method="POST" action="{{ route('feedback') }}"
                                  success-msg="Your message has been sent."
                                  fail-msg="Bugger, something broke down. Please email us directly at keychest@enigmabridge.com">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label>
                                        Email (if you want)
                                    </label>
                                    <input id="email" class="form-control" type="email" name="email">
                                    <div class="help-block"></div></div>
                                <div class="form-group">
                                    <label>
                                        I would find quite useful:
                                    </label><textarea id="message" class="form-control" rows="4" cols="50" required="" name="message"></textarea>
                                    <div class="help-block"></div></div>
                                <button class="bloc-button btn btn-lg btn-block btn-rich-electric-blue" type="submit">
                                    Add my vote for these features
                                </button>
                            </form>
                        </div>
                    </div>
                    <p>
                        Feel free to email us at <a href="mailto:keychest@enigmabridge.com">keychest@enigmabridge.com</a>, if you have in mind particular details of a feature you’d like to see.
                    </p>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <p>
                        We are Enigma Bridge, 20 Bridge St, Cambridge, CB2 1UF, United Kingdom and we read keychest@enigmabridge.com
                    </p>
                </div>
            </div>
        </div>
    </div>
    <!-- bloc-3 END -->

    <!-- ScrollToTop Button -->
    <a class="bloc-button btn btn-d scrollToTop" onclick="scrollToTarget('1')"><span class="fa fa-chevron-up"></span></a>
    <!-- ScrollToTop Button END-->


    <!-- Footer - bloc-7 -->
    <div class="bloc bgc-white tc-outer-space" id="bloc-7">

    </div>
    <!-- Footer - bloc-7 END -->

@endsection

