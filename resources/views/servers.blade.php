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
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">Servers</a></li>
                        <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">Auto Sub-domains</a></li>
                        {{--<li class="pull-right"><a href="#" class="text-muted"><i class="fa fa-gear"></i></a></li>--}}
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_1">
                            <server-tables></server-tables>
                        </div>
                        <!-- /.tab-pane -->
                        <div class="tab-pane" id="tab_2">
                            <subdomains></subdomains>
                        </div>
                        <!-- /.tab-pane -->
                    </div>
                    <!-- /.tab-content -->
                </div>
        </div>
    </div>
@endsection
