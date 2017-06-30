@extends('layouts.app')

@section('content-nav')
    {{--<li><a onclick="scrollToTarget('#learn')">Learn more</a></li>--}}
@endsection

@section('content')
    <div class="bloc bloc-fill-screen tc-onyx bgc-white l-bloc" id="intro" style="height: 400px;">
        <div class="container bloc-sm">
            <div class="row">

                <div class="col-sm-12">
                    <img src="/images/logo2-rgb_keychest.png" class="img-responsive center-block" width="300">
                    <h3 class="text-center mg-lg hero-bloc-text-sub  tc-rich-electric-blue">
                        Track and plan for 100% HTTPS uptime
                    </h3>
                    <p class="text-center">
                        This is a small documentation page, which provides information that you may find interesting if you use, manage, or audit your certificates.&nbsp;
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-4">
                    <a href="keychest_spot_check"><img src="/images/worldAtHand_EB.png" class="img-responsive" /></a>
                    <h3 class="mg-md tc-rich-electric-blue text-center">
                        <a class="ltc-rich-electric-blue" href="keychest_spot_check">Keychest spot check</a><br>
                    </h3>
                    <p>
                        Learn more about what our spot check is for and how to use it.
                    </p>
                </div>
                <div class="col-sm-4">
                    <a href="letsencrypt_numbers_to_know"><img src="/images/secure_gateway_370x290.jpg" class="img-responsive" /></a>
                    <h3 class="mg-md tc-rich-electric-blue text-center">
                        <a class="ltc-rich-electric-blue" href="letsencrypt_numbers_to_know">Let&rsquo;s Encrypt in numbers</a><br>
                    </h3>
                    <p>
                        We have compiled all the information we could find so you can decide if Let&rsquo;s Encrypt certificates are for you.
                    </p>
                </div>
                <div class="col-sm-4">
                    <a href="understand_spot_checks"><img src="/images/blue_lock_370x290.png" class="img-responsive" /></a>
                    <h3 class="mg-md tc-rich-electric-blue text-center">
                        <a class="ltc-rich-electric-blue" href="understand_spot_checks">Understand spot check results</a><br>
                    </h3>
                    <p>
                        A detailed explanation of the results of our KeyChest spot checks.
                    </p>
                </div>
            </div>
        </div>
    </div>
    <!-- bloc-2 END -->

    <!-- learn -->
    @include('partials.landing.learn')
    <!-- learn END -->

    <!-- bloc-3 -->
    @include('partials.landing.feedbackform')
    <!-- bloc-3 END -->

    <!-- ScrollToTop Button -->
    <a class="bloc-button btn btn-d scrollToTop" onclick="scrollToTarget('1')"><span class="fa fa-chevron-up"></span></a>
    <!-- ScrollToTop Button END-->

    <!-- Footer - bloc-7 -->
    @include('partials.landing.footer')
    <!-- Footer - bloc-7 END -->

@endsection

