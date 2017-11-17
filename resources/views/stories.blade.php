@extends('layouts.app')

@section('content-nav')
    {{--<li><a onclick="scrollToTarget('#learn')">Learn more</a></li>--}}
@endsection

@section('content')
    <div class="bloc bloc-fill-screen tc-onyx bgc-white l-bloc" id="intro" style="height: 400px;">
        <div class="container bloc-sm">
            <div class="row">

                <div class="col-sm-12">
                    {{--<img src="/images/logo2-rgb_keychest.png" alt="Certificate expiry monitoring KeyChest logo" class="img-responsive center-block" width="300">--}}
                    <h3 class="text-center mg-sm hero-bloc-text-sub  tc-rich-electric-blue">
                        100% HTTPS uptime with KeyChest
                    </h3>
                    <h4 class="text-center mg-lg tc-onyx">
                        One place for your TLS/HTTPS certificates
                    </h4>
                    <p class="text-center">
                        We have created a few stories with information about using, managing, and auditing your certificates.&nbsp;
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-4">
                    <a href="/content/keychest_spot_check"><img src="/images/worldAtHand_EB.png" alt="Certificate monitoring KeyChest - world at hand" class="img-responsive" /></a>
                    <h3 class="mg-md tc-rich-electric-blue text-center">
                        <a class="ltc-rich-electric-blue" href="/content/keychest_spot_check">KeyChest spot check</a><br>
                    </h3>
                    <p>
                        Learn more about what our spot check is for and how to use it.
                    </p>
                </div>
                <div class="col-sm-4">
                    <a href="/content/letsencrypt_numbers_to_know"><img src="/images/secure_gateway_370x290.jpg" alt="Certificate monitoring KeyChest - secure gateway" class="img-responsive" /></a>
                    <h3 class="mg-md tc-rich-electric-blue text-center">
                        <a class="ltc-rich-electric-blue" href="/content/letsencrypt_numbers_to_know">Let&rsquo;s Encrypt in numbers</a><br>
                    </h3>
                    <p>
                        We have compiled all the information we could find so you can decide if Let&rsquo;s Encrypt certificates are for you.
                    </p>
                </div>
                <div class="col-sm-4">
                    <a href="/content/understand_spot_checks"><img src="/images/blue_lock_370x290.png" alt="Certificate monitoring KeyChest lock" class="img-responsive" /></a>
                    <h3 class="mg-md tc-rich-electric-blue text-center">
                        <a class="ltc-rich-electric-blue" href="/content/understand_spot_checks">Understand spot check results</a><br>
                    </h3>
                    <p>
                        A detailed explanation of the results of our KeyChest spot checks.
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-4">
                    <a href="/content/letsencrypt_uptime"><img src="/images/letsencrypt-minutes-of-partial-disruptions-and-overall-uptime.png" alt="Certificate
                    monitoring KeyChest - Letsencrypt uptime" class="img-responsive" /></a>
                    <h3 class="mg-md tc-rich-electric-blue text-center">
                        <a class="ltc-rich-electric-blue" href="/content/letsencrypt_uptime">Let's Encrypt Uptime Analysis</a><br>
                    </h3>
                    <p>
                        We have analyzed status logs of Let's Encrypt production systems from January 2016 till September 2017
                        to understand the reliability of Let's Encrypt issuance and validation services.
                    </p>
                </div>
                <div class="col-sm-4">
                    <a href="/content/infineon_key_generation"><img src="/images/TPM_infineon.png" alt="Certificate monitoring KeyChest - flawed key generation in Infineon security chips" class="img-responsive" /></a>
                    <h3 class="mg-md tc-rich-electric-blue text-center">
                        <a class="ltc-rich-electric-blue" href="/content/infineon_key_generation">ROCA bug - quantum effects</a><br>
                    </h3>
                    <p>
                        Masaryk University has discovered a serious fault in RSA key generation inside Infineon security
                        chips, which are used by tens of millions of devices and thousands of companies.
                    </p>
                </div>
                <div class="col-sm-4">
{{--
                    <a href="/content/understand_spot_checks"><img src="/images/blue_lock_370x290.png" alt="Certificate monitoring KeyChest lock" class="img-responsive" /></a>
                    <h3 class="mg-md tc-rich-electric-blue text-center">
                        <a class="ltc-rich-electric-blue" href="/content/understand_spot_checks">Understand spot check results</a><br>
                    </h3>
                    <p>
                        A detailed explanation of the results of our KeyChest spot checks.
                    </p>
--}}
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

