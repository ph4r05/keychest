@extends('adminlte::layouts.app')

@section('htmlheader_title')
    {{ trans('admin.ip_servers') }}
@endsection

@section('contentheader_title')
    {{ trans('admin.ip_servers') }}
@endsection

@section('contentheader_description')
    {{ trans('admin.ip_servers_desc') }}
@endsection

@section('main-content')
    <div class="servers-wrapper container-fluid spark-screen">
        <ip_servers></ip_servers>
    </div>
@endsection
