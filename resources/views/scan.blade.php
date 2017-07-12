@extends('adminlte::layouts.app')

@section('htmlheader_title')
    {{ trans('admin.scan') }}
@endsection

@section('contentheader_title')
    {{ trans('admin.scan') }} <help-trigger id="scanHelpModal"></help-trigger>
@endsection

@section('contentheader_description')
    {{ trans('admin.scan_desc') }}
@endsection

@section('main-content')
    @include('partials.spotcheck_info')

    <div class="servers-wrapper container-fluid spark-screen">
        <div class="row">
            <div class="servers-tab col-md-12 ">

                <quicksearch></quicksearch>

            </div>
        </div>
    </div>
@endsection
