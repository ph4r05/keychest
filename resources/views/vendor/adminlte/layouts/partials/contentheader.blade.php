<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        @yield('contentheader_title', 'Page Header here')
        <small>@yield('contentheader_description')</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-university"></i> {{ trans('admin.home') }}</a></li>
        <li class="active">@yield('contentheader_here', 'Current page') </li>
    </ol>
</section>