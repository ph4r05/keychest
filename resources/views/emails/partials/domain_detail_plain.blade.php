@foreach($certs as $cert)
    @foreach($cert->tls_watches as $watch)
{{ $watch->host_port }}        {{ $watch->tls_ips_are_all ? '' : 'multiple certificates' }}
      {{ $cert->valid_to->diffForHumans() }} ({{ $cert->valid_to->format('M j, Y H:i:s ') }})
    @endforeach
@endforeach
