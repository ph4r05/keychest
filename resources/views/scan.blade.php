@extends('adminlte::layouts.app')

@section('htmlheader_title')
    {{ trans('admin.scan') }}
@endsection

@section('contentheader_title')
    {{ trans('admin.scan') }} <help-trigger id="scanHelpModal"></help-trigger>
@endsection

@section('contentheader_description')
    {{ trans('admin.scan_desc') }}
@endsection

@section('main-content')
    <help-modal id="scanHelpModal" title="Spot check">
        <p>
            Spot check is a powerful tool for quick assessment of the SSL/TLS configuration of your servers.
        </p>
        <p>
            It resolves the DNS name you provide and runs a series of tests against that IP address -
            no automatic redirect, but it shows a redirect, if detected, so you can quickly run another
            check against the detected server.
        </p>
        <p>
            The list of spot check tests:
            <ul>
            <li>certificate expiration - how many days till the certificate expires;</li>
            <li>downtime - downtime during the last 2 years; CT logs data amended with server checks if this data is available;</li>
            <li>trust chain - whether the server provides a complete chain of certificates needed
                for validation;</li>
            <li>certificate issuer - it shows the name of the certificate issuer (if set);</li>
            <li>list of neighbors - the list of all names in the certificate;</li>
            <li>hostname match - whether the name(s) in the certificate contain the server's name;</li>
            <li>SSL detection - if your server uses insecure version SSL2 or SSL3, it will be flagged; and</li>
            <li>HSTS - if the HSTS (HTTP Strict Server Security) is enabled.</li>
        </ul>
        </p>
    </help-modal>

    <div class="servers-wrapper container-fluid spark-screen">
        <div class="row">
            <div class="servers-tab col-md-12 ">

                <quicksearch></quicksearch>

            </div>
        </div>
    </div>
@endsection
