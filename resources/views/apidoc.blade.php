@extends('adminlte::layouts.app')

@section('header-scripts')
    <script type="text/javascript" src="https://s3.amazonaws.com/assets.freshdesk.com/widget/freshwidget.js"></script>
    <style type="text/css" media="screen, projection">
        @import url(https://s3.amazonaws.com/assets.freshdesk.com/widget/freshwidget.css);
    </style>
@endsection

@section('htmlheader_title')
    {{ trans('admin.apidoc') }}
@endsection

@section('contentheader_title')
    {{ trans('admin.apidoc') }}
@endsection

@section('contentheader_description')
    {{ trans('admin.apidoc_desc') }}
@endsection

@section('main-content')
    @include('partials.apidoc')
    @include('partials.share', ['path' => url('/register')])
@endsection
