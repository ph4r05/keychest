<!DOCTYPE html>
<html lang="en">

@section('htmlheader')
    @include('adminlte::layouts.partials.htmlheader')
@show

<body class="skin-blue-light sidebar-mini">
<div id="app" v-cloak>
    <div class="wrapper">
        @yield('content')
    </div><!-- ./wrapper -->
</div>

<!-- Main Footer -->
<footer class="main-footer" style="margin-left: 0">
    <div class="pull-right hidden-xs">
    </div>
    <strong>Copyright &copy; 2017 <a href="http://enigmabridge.com">enigmabridge.com</a>.</strong>

    <div class="ph4-modal">
        <div class="ph4-modal-wrap"></div>
    </div>
</footer>

@section('scripts')
    @include('adminlte::layouts.partials.scripts')
@show

</body>
</html>
