@extends('adminlte::layouts.app')

@section('htmlheader_title')
	{{ trans('adminlte_lang::message.home') }}
@endsection

@section('contentheader_title')
	{{ trans('admin.dashboard') }}
@endsection

@section('contentheader_description')
	{{ trans('admin.dashboard_desc') }}
@endsection

@section('main-content')
	<div class="container-fluid spark-screen">

		<!-- dashboard -->
		<div class="row">
			<div class="col-md-12">
				<dashboard_cio></dashboard_cio>
			</div>
		</div>

	</div>
@endsection
