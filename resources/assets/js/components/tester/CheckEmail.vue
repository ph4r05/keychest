<template>
    <div class="subdomains-wrapper">
        <h4>Send us e-mail</h4>

        <p>
            We have created an email responder for easy testing of <strong>PGP</strong> keys and <strong>S/MIME</strong>
            certificates. Simply send a digitally signed email or your PGP key to the address below and the
            responder will reply with a test report within a couple of minutes.
        </p>
        <p>
            <strong>Note:</strong> If you want to test a PGP key, you may want to rather send your public key as an
            attachment. Most email clients support this.
        </p>

        <div class="alert alert-info text-center">
            <h2 class="h2-nomarg"><a href="mailto:roca@keychest.net" class="a-nounder">roca@keychest.net</a></h2>
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