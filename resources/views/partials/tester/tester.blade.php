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
<div class="row">
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

                    <div class="col-md-12">
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

                        <p>
                            This service is provided by <a target="_blank" href="https://enigmabridge.com"><b>Enigma Bridge</b></a> and its <a target = "_blank" href="https://keychest.net"><b>KeyChest</b></a> - a key management platform with a simple certificate expiry monitoring with automatic key enrolment and discovery. Its certificate renewal capabilities significantly reduce labor and management cost. It uses a unique secure hardware encryption platform for physical security of private keys.
                        </p>
                        <p><b>Update (20th October)</b>: Gemalto IDPrime .NET smart cards have been generating weak RSA
                            keys since 2008 or earlier - <a target="_blank" href="https://dan.enigmabridge.com/roca-vulnerability-impact-on-gemalto-idprime-net-smart-cards/">ROCA
                                vulnerability impact on Gemalto IDPrime .NET smart cards</a>.</p>
                        <p><b>Update (24th October)</b>: <a target="_blank" href="https://crocs.fi.muni.cz/">Researchers
                                from Masaryk University</a> requested changes to texts explaining test results. We are
                            updating these to provide more accurate guidance. Please visit their web page at <a target="_blank"
                        href="https://crocs.fi.muni.cz/public/papers/rsa_ccs17">ROCA: Vulnerable RSA generation</a> for a detailed
                        description of the impact of the ROCA vulnerability.</p>
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
