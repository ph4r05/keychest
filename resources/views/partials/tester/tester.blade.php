<!-- Logo -->
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <a class="" href="https://keychest.net">
                    <img src="/images/logo2-rgb_keychest.png" alt="Certificate monitoring KeyChest logo" height="30" title="Keychest">
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Intro info -->
<div class="row" style="font-family:Verdana">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row">

                    <div class="col-md-8 col-md-offset-2 text-center">
                        <h1 class="tc-rich-electric-blue">ROCA Vulnerability Test Suite</h1>
                    </div>
                    <div class="col-md-8 col-md-offset-2 text-center">
                        <h3>Information and tools to test RSA keys for the ROCA vulnerability</h3>
                    </div>

                    <div class="col-md-6">
                        <p>
                            The ROCA vulnerability has been discovered by researchers at <a target="_blank"
                            href="https://roca.crocs.fi.muni.cz">
                            Masaryk University (Brno, Czech Republic)</a>. As two of the researchers are
                            also affiliated with <a target="_blank" href="https://enigmabridge.com">Enigma Bridge</a> we subsequently
                            integrated a ROCA detection tool within this test suite. It allows users of affected products to verify
                            security of their encryption keys.
                        </p>
                        <p>
                            This test suite provides information about the ROCA vulnerability, which is caused by
                            an error in RSA key generation in Infineon security chips. These computer chips are
                            used in a number of products and applications as detailed in the ROCA vulnerability
                            summary below.
                        </p>

                        <p>
                            You can use this test suit to check your RSA keys in a text form, by uploading a keystore
                            in one of the supported types, or by sending an email with a digital signature (S/MIME)
                            or your PGP key to an email responder. Use the form below to select the most suitable
                            method.
                        </p>

                        <p>
                            If you experience difficulties or errors on this page, please let us know via our
                            <a href="https://enigmabridge.freshdesk.com/support/tickets/new">support system</a>.
                        </p>

                        <p>
                            <strong>Privacy notice:</strong> Any data you provide on this page is deleted as soon
                            as we complete a requested test. We do not keep your keys or any other data generated
                            during testing.
                        </p>

                        <p><b>Update (20th October)</b>: Gemalto IDPrime .NET smart cards have been generating weak RSA
                            keys since 2008 or earlier - <a target="_blank" href="https://dan.enigmabridge.com/roca-vulnerability-impact-on-gemalto-idprime-net-smart-cards/">ROCA
                                vulnerability impact on Gemalto IDPrime .NET smart cards</a>.</p>
                        <p><b>Update (24th October)</b>: <a target="_blank" href="https://crocs.fi.muni.cz/">Researchers
                                from Masaryk University</a> requested changes to texts explaining test results. We are
                            updating these to provide more accurate guidance. Please visit their web page at <a target="_blank"
                        href="https://crocs.fi.muni.cz/public/papers/rsa_ccs17">ROCA: Vulnerable RSA generation</a> for a detailed
                        description of the impact of the ROCA vulnerability.</p>
                        <p><b>Update (14th November)</b>: The Spanish government has said it would "deactivate" all
                            electronic ID-card (DNIe) certificates issued after May 2015. It hasn't happened yet.
                        The official statement in Spanish is at
                            <a target="_blank" href="https://www.dnielectronico.es/PortalDNIe/">"Direccion General de la Policia - DNI y Pasaporte" portal</a>.</p>
                    </div>
                    <div class="col-md-6" style="background-color: #ecf0f5;">
                        <p>
                            This service is provided by <a target="_blank" href="https://enigmabridge.com">Enigma Bridge</a>
                            and powered by <a target = "_blank" href="https://keychest.net/#detail">KeyChest</a>.
                            It uses <a target="_blank" href="https://github.com/crocs-muni/roca">the official ROCA detection tool</a>.
                        </p>
                        <p>
                            KeyChest is a certificate management service for HTTPS certificates. It automatically discovers
                            new certificates and adds them to its reports. The certificate renewal system uses an Ansible
                            integration and removes the need for keeping Let's Encrypt clients up-to-date on each of
                            your servers. It simply provides keys and certificate when needed.
                        </p>

                        <p>
                            The <a target="_blank" href="https://api.enigmabridge.com/api/?shell#keychest">KeyChest
                                RESTful API</a> allows automation of independent monitoring with a self-registration of
                            new clients. It provides expiry information for each detected IP address.
                        </p>

                        <a href="{{url('/register')}}"><img src="/images/keychest_dashboard.png" class="img-responsive"/></a>

                        <br/>
                        <p>
                            It's free here as a cloud service. Just click the image above, or
                            <a href="{{url('/register')}}">this link to register</a>.
                        </p>

                        <p>
                            You can learn more about KeyChest at the <a href="{{url("/#detail")}}">landing page of this
                                website</a>.
                        </p>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading placeholder -->
@component('partials.box', [
    'title' => 'ROCA vulnerability summary',
    'boxId' => 'roca-placeholder'
])
    <span>Loading, please wait...</span>
@endcomponent

<!-- Vue tester -->
<tester></tester>
