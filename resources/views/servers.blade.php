@extends('adminlte::layouts.app')

@section('htmlheader_title')
    {{ trans('admin.servers') }}
@endsection

@section('contentheader_title')
    {{ trans('admin.servers') }}
@endsection

@section('contentheader_description')
    {{ trans('admin.servers_desc') }}
@endsection

@section('main-content')
    <div class="servers-wrapper container-fluid spark-screen">
        <div class="row">
            <div class="servers-tab col-md-12 ">

                <server-tables></server-tables>

            </div>
        </div>
    </div>
@endsection
