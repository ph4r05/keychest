@extends('adminlte::layouts.app')

@section('htmlheader_title')
	{{ trans('adminlte_lang::message.home') }}
@endsection

@section('contentheader_title')
	<span class="tc-rich-electric-blue">{{ trans('admin.dashboard') }}</span>
@endsection

@section('contentheader_description')
	<span class="tc-onyx">{{ trans('admin.dashboard_desc') }}</span>
@endsection

@section('main-content')
	<div class="container-fluid spark-screen">

		<!-- dashboard -->
		<div class="row">
			<div class="col-md-12">
				<dashboard></dashboard>
			</div>
		</div>

	</div>
@endsection
