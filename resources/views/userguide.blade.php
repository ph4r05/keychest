@extends('adminlte::layouts.app')

@section('htmlheader_title')
    {{ trans('admin.userguide') }}
@endsection

@section('contentheader_title')
    {{ trans('admin.userguide') }}
@endsection

@section('contentheader_description')
    {{ trans('admin.userguide_desc') }}
@endsection

@section('main-content')
    @include('partials.userguide')
@endsection
