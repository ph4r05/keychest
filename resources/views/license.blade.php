@extends('adminlte::layouts.app')

@section('header-scripts')
    <script type="text/javascript" src="https://s3.amazonaws.com/assets.freshdesk.com/widget/freshwidget.js"></script>
    <style type="text/css" media="screen, projection">
        @import url(https://s3.amazonaws.com/assets.freshdesk.com/widget/freshwidget.css);
    </style>
@endsection

@section('htmlheader_title')
    {{ trans('admin.license') }}
@endsection

@section('contentheader_title')
    <span class="tc-rich-electric-blue">{{ trans('admin.license') }}</span>
@endsection

@section('contentheader_description')
            <span class="tc-onyx">{{ trans('admin.license_desc') }}</span>
@endsection

@section('main-content')
    @include('partials.license')
    @include('partials.share', ['path' => url('/register')])
@endsection
