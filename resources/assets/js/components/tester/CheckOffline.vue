<template>
    <div>
        <h4>Install Python test</h4>

        <p>
            If want to test your keys privately on your computers, or integrate the test into your application, please
            install our Python test implementation.

        </p>

        <p>
            The Python test supports:
        </p>

        <ul>
            <li>X509 Certificate, PEM/DER encoded;</li>
            <li>RSA PEM encoded private key, public key;</li>
            <li>SSH public key;</li>
            <li>ASC encoded PGP key, *.pgp, *.asc;</li>
            <li>Java Key Store file (JKS);</li>
            <li>PKCS7 signature with user certificate;</li>
            <li>APK Android application;</li>
            <li>LDIFF file - LDAP database dump. Any field ending with ";binary::" is attempted to decode as X509 certificate</li>
            <li>Text file (*.txt) with an RSA key modulus per line; the modulus can be
                a) a base64 encoded number, b) a hex coded number, c) a decimal coded number;</li>
            <li>JSON file with RSA keys, a record per line; supported records are RSA moduli
                (the "mod" key), certificates (the "cert" key) in the base64 encoded DER format, or an array of certificates
                (the "certs" key) in the base64 encoded DER format. </li>
        </ul>

        <h3>Installation</h3>
        <p>
            The detection tool is implemented in Python.
        </p>

        <div class="alert alert-info">
            <span class="code-block">pip install iont-detect</span>
        </div>

        <h3>Usage</h3>
        <div class="alert alert-info">
            <span class="code-block">iont-detect --help
iont-detect my-cert.pem
iont-detect my-pgp-key.asc</span>
        </div>

        <p>
            For more information please refer to the detector GIT repository and its readme file at:
            <a href="https://github.com/crocs-muni">https://github.com/crocs-muni</a>
        </p>
    </div>

</template>
<script>

    export default {

    }
</script>
<style scoped>
    .h2-nomarg {
        margin: 0;
    }

    .a-nounder {
        text-decoration: none;
    }
</style>