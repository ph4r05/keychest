<table>
    <thead>
        <tr style="text-align: left">
            <th>Domain</th>
            <th>IPs</th>
            <th>Expiration</th>
        </tr>
    </thead>
    <tbody>
    @foreach($certs as $cert)
        @foreach($cert->tls_watches as $watch)
            <tr>
                <td>{{ $watch->host_port }}</td>
                <td>{{ $watch->tls_ips_are_all ? 'All ('.$watch->tls_ips->count().')' : join(', ', $watch->tls_ips->all()) }}</td>
                <td>{{ $cert->valid_to }} ({{ $cert->valid_to->diffForHumans() }})</td>
            </tr>
        @endforeach
    @endforeach
    </tbody>
</table>
