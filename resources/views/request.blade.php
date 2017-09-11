@extends('adminlte::layouts.app')

@section('htmlheader_title')
    {{ trans('admin.request') }}
@endsection

@section('contentheader_title')
    <span class="tc-rich-electric-blue">{{ trans('admin.request') }}</span>
@endsection

@section('contentheader_description')
    <span class="tc-onyx">{{ trans('admin.request_desc') }}</span>
@endsection

@section('main-content')
    @include('partials.request')
@endsection
