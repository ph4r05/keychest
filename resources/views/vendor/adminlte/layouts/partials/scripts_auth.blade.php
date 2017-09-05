<!-- Compiled app javascript -->
<script src="{{ url('/ws/socket.io/socket.io.js')  }}"></script>
<script src="{{ url (mix('/js/manifest.js')) }}"></script>
<script src="{{ url (mix('/js/vendor.js')) }}"></script>
<script src="{{ url (mix('/js/polyapp-user.js')) }}"></script>
@yield('footer-scripts')
{!! survivor() !!}
