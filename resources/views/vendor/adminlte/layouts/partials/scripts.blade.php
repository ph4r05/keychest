<!-- REQUIRED JS SCRIPTS -->

<!-- JQuery and bootstrap are required by Laravel 5.3 in resources/assets/js/bootstrap.js-->
<!-- Laravel App -->
<!-- Compiled app javascript -->
<script src="{{ url (mix('/js/manifest.js')) }}"></script>
<script src="{{ url (mix('/js/vendor.js')) }}"></script>
<script src="{{ url (mix('/js/polyapp-user.js')) }}"></script>

{!! survivor() !!}

<!-- Optionally, you can add Slimscroll and FastClick plugins.
      Both of these plugins are recommended to enhance the
      user experience. Slimscroll is required when using the
      fixed layout. -->
