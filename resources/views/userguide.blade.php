@extends('adminlte::layouts.app')

@section('htmlheader_title')
    {{ trans('admin.userguide') }}
@endsection

@section('contentheader_title')
    <span class="tc-rich-electric-blue">{{ trans('admin.userguide') }}</span>
@endsection

@section('contentheader_description')
    <span class="tc-onyx">{{ trans('admin.userguide_desc') }}</span>
@endsection

@section('main-content')
    @include('partials.userguide')
@endsection
