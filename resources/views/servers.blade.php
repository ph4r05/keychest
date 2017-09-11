@extends('adminlte::layouts.app')

@section('htmlheader_title')
    {{ trans('admin.servers') }}
@endsection

@section('contentheader_title')
    <span class="tc-rich-electric-blue">{{ trans('admin.servers') }}</span>
@endsection

@section('contentheader_description')
    <span class="tc-onyx">{{ trans('admin.servers_desc') }}</span>
@endsection

@section('main-content')
    <div class="servers-wrapper container-fluid spark-screen">
        <servers></servers>
    </div>
@endsection
