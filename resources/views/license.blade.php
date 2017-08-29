@extends('adminlte::layouts.app')

@section('htmlheader_title')
    {{ trans('admin.license') }}
@endsection

@section('contentheader_title')
    {{ trans('admin.license') }}
@endsection

@section('contentheader_description')
    {{ trans('admin.license_desc') }}
@endsection

@section('main-content')
    @include('partials.license')
    @include('partials.share', ['path' => 'https://keychest.net/register'])
@endsection
