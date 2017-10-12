<template>
    
    <div>
        <p>
            There is also a way to test your public keys privately offline with use of our detection tool.
        </p>

        <p>
            Supported formats:
        </p>

        <ul>
            <li>X509 Certificate, PEM/DER encoded</li>
            <li>RSA PEM encoded private key, public key</li>
            <li>SSH public key</li>
            <li>ASC encoded PGP key, *.pgp, *.asc</li>
            <li>Java Key Store file (JKS). </li>
            <li>PKCS7 signature with user certificate</li>
            <li>APK Android application</li>
            <li>LDIFF file - LDAP database dump. Any field ending with ";binary::" is attempted to decode as X509 certificate</li>
            <li>One modulus per line text file *.txt, modulus can be
                a) base64 encoded number, b) hex coded number, c) decimal coded number</li>
            <li>JSON file with moduli, one record per line, record with modulus has
                key "mod", certificate(s) with key "cert" / array of certificates with key "certs" are supported, base64 encoded DER. </li>
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