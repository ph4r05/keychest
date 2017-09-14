<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <span class="tc-rich-electric-blue">@yield('contentheader_title', 'Page Header here')</span>
        <small><span class="tc-onyx">@yield('contentheader_description')</span></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-university"></i> {{ trans('admin.home') }}</a></li>
        <li class="active">@yield('contentheader_here', 'Current page') </li>
    </ol>
</section>