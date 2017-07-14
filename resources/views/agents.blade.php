@extends('adminlte::layouts.app')

@section('htmlheader_title')
    {{ trans('admin.agents') }}
@endsection

@section('contentheader_title')
    {{ trans('admin.agents') }}
@endsection

@section('contentheader_description')
    {{ trans('admin.agents_desc') }}
@endsection

@section('main-content')
    @include('partials.agents')
@endsection
