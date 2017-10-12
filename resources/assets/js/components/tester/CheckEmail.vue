<template>
    <div class="subdomains-wrapper">
        <h3>Send us an e-mail</h3>

        <p>
            For easy testing of <strong>PGP</strong> keys and <strong>SMIME</strong> certificates
            you can send us a signed email to the account below, we will check it and send you report back.
        </p>

        <div class="alert alert-info text-center">
            <h2 class="h2-nomarg"><a href="mailto:test@keychest.net" class="a-nounder">test@keychest.net</a></h2>
        </div>

        <p>
            <small><strong>Note:</strong> for PGP email its better to attach your public key to
                the email, most of the emailing clients supports this.</small>
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