{{-- Main tester template --}}
@extends('vendor.adminlte.layouts.landing-tpl')

@section('content')

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper-full">

        <!-- Main content -->
        <section class="content" style="background-color: #ecf0f5;">
            <div>
                <div class="container">
                    @include('partials.tester.tester')
                </div>
            </div>

            <!-- learn -->
            {{--@include('partials.landing.learn')--}}
            <!-- learn END -->

            <!-- bloc-3 -->
            {{--@include('partials.landing.feedbackform')--}}
            <!-- bloc-3 END -->

            <!-- ScrollToTop Button -->
            {{--<a class="bloc-button btn btn-d scrollToTop" onclick="scrollToTarget('1')"><span class="fa fa-chevron-up"></span></a>--}}
            <!-- ScrollToTop Button END-->

            <!-- Footer - bloc-7 -->
            @include('partials.landing.footer')
            <!-- Footer - bloc-7 END -->

        </section><!-- /.content -->
    </div><!-- /.content-wrapper -->

@endsection

