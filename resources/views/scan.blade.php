@extends('adminlte::layouts.app')

@section('htmlheader_title')
    {{ trans('admin.scan') }}
@endsection

@section('contentheader_title')
    <span class="tc-rich-electric-blue">{{ trans('admin.scan') }} <help-trigger id="scanHelpModal"></help-trigger></span>
@endsection

@section('contentheader_description')
    <span class="tc-onyx">{{ trans('admin.scan_desc') }}
@endsection

@section('main-content')
    <div class="servers-wrapper container-fluid spark-screen">
        <div class="row">
            <div class="servers-tab col-md-12 ">

                <quicksearch></quicksearch>

            </div>
        </div>
    </div>
    @include('partials.spotcheck_info')
@endsection
