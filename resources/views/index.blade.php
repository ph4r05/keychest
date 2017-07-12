@extends('layouts.app')

@section('content-nav')
    {{--<li><a onclick="scrollToTarget('#learn')">Learn more</a></li>--}}
@endsection

@section('content')
    <!-- loading placeholder -->
	@include('partials.landing.loading_placeholder')

    <!-- Vue search component -->
    @include('partials.spotcheck_info')
    <quicksearch-main></quicksearch-main>

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

