@extends('adminlte::layouts.app')

@section('htmlheader_title')
    {{ trans('admin.cost-management') }}
@endsection

@section('contentheader_title')
    {{ trans('admin.cost-management') }}
@endsection

@section('contentheader_description')
    {{ trans('admin.cost_management_desc') }}
@endsection

@section('main-content')
    <div class="management-wrapper container-fluid spark-screen">
        <cost_management></cost_management>
    </div>
@endsection
