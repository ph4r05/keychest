@extends('adminlte::layouts.app')

@section('htmlheader_title')
    {{ trans('admin.enterprise') }}
@endsection

@section('contentheader_title')
    {{ trans('admin.enterprise') }}
@endsection

@section('contentheader_description')
    {{ trans('admin.enterprise_desc') }}
@endsection

@section('main-content')
    @include('partials.enterprise')
@endsection
