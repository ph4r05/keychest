@extends('adminlte::layouts.app')

@section('htmlheader_title')
    {{ trans('admin.management') }}
@endsection

@section('contentheader_title')
    {{ trans('admin.management') }}
@endsection

@section('contentheader_description')
    {{ trans('admin.management_desc') }}
@endsection

@section('main-content')
    <div class="management-wrapper container-fluid spark-screen">
        <management></management>
    </div>
@endsection
