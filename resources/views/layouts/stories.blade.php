@extends('layouts.app')


@section('content')
    <!-- story -->
    @yield('story')
    <!-- story END -->

    <!-- ScrollToTop Button -->
    <a class="bloc-button btn btn-d scrollToTop" onclick="scrollToTarget('1')"><span class="fa fa-chevron-up"></span></a>
    <!-- ScrollToTop Button END-->

    <!-- Footer - bloc-7 -->
    @include('partials.landing.footer')
    <!-- Footer - bloc-7 END -->

@endsection

