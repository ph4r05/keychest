@extends('layouts.app')

@section('content')
    <div class="bloc bloc-fill-screen tc-onyx bgc-white l-bloc" id="intro" style="height: 400px;">
        <div class="container">
            <div class="row">

                <div class="col-sm-12">
                    <img src="/images/logo2-rgb_keychest.png" alt="Certificate expiry monitoring KeyChest logo" class="img-responsive center-block" width="300">
                    <h3 class="text-center mg-lg hero-bloc-text-sub  tc-rich-electric-blue">
                        @yield('header-title')
                    </h3>
                    @yield('header-subtitle')
                </div>
            </div>

            <div class="row">
                <div class="col-sm-8 col-sm-offset-2">
                    @yield('content-body')
                </div>
            </div>
            @yield('content-body-outer')

        </div>

        <div class="container fill-bloc-bottom-edge">
            <div class="row row-no-gutters">
                <div class="col-sm-12">
                    <a id="scroll-hero" class="blocs-hero-btn-dwn" href="https://keychest.net/#"><span class="fa fa-chevron-down"></span></a>
                </div>
            </div>
        </div>

    </div>

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

