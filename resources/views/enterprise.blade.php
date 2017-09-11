@extends('adminlte::layouts.app')

@section('htmlheader_title')
    {{ trans('admin.enterprise') }}
@endsection

@section('contentheader_title')
    <span class="tc-rich-electric-blue">{{ trans('admin.enterprise') }} </span>
@endsection

@section('contentheader_description')
    <span class="tc-onyx"> {{ trans('admin.enterprise_desc') }} </span>
@endsection

@section('main-content')
    @include('partials.enterprise')
@endsection
