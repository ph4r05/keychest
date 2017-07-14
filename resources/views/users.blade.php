@extends('adminlte::layouts.app')

@section('htmlheader_title')
    {{ trans('admin.users') }}
@endsection

@section('contentheader_title')
    {{ trans('admin.users') }}
@endsection

@section('contentheader_description')
    {{ trans('admin.users_desc') }}
@endsection

@section('main-content')
    @include('partials.users')
@endsection
