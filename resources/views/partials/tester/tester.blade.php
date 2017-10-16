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
                                Masaryk University (Brno, Czech Republic)</a>. Some members of the research team are
                            also affiliated with <a href="https://enigmabridge.com">Enigma Bridge</a>
                            and <a href="http://www.dais.unive.it/~acadia/">Ca' Foscari University of Venice</a>.
                            A subsequent close
                            cooperation resulted in this test suite, which allows users of affected products to verify
                            whether their encryption keys are still secure.
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
