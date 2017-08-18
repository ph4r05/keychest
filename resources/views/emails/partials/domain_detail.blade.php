<table>
    <thead>
        <tr style="text-align: left">
            <th>Domain</th>
            <th></th>
            <th>Expiration</th>
        </tr>
    </thead>
    <tbody>
    @foreach($certs as $cert)
        @foreach($cert->tls_watches as $watch)
            <tr>
                <td>{{ $watch->host_port }}</td>
                <td>{{ $watch->tls_ips_are_all ? '' : 'multiple certificates' }}</td>
                <td>{{ $cert->valid_to->format('M j, Y H:i:s ') }} ({{ $cert->valid_to->diffForHumans() }})</td>
            </tr>
        @endforeach
    @endforeach
    </tbody>
</table>
