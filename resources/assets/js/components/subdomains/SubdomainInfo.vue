<template>
    <!-- Subdomain watch modal info -->
    <help-modal id="subdomainsInfoModal" title="Automatic server discovery">
        <p>
            A unique feature of KeyChest allows it to automatically discover new servers in your Active Domains.
            For example, let's say you have a domain "company.com" with tens of subdomains. If you want to discover
            all certificates for those subdomains, you just add "company.com" to the list of Active Domains. The list
            of servers below will periodically refresh and show all servers, including those with a fresh first certificate.
        </p>
        <p>
            If you enable the Watch Now&trade; feature, KeyChest will automatically add all new servers
            for monitoring. Without this option, you have to start monitoring new servers manually for them to appear in
            the Dashboard. The Watch Now&trade; feature will start testing servers on port 443 (HTTPS).
        </p>
        <p>
            Note: Detection of servers in active domains happens:
        </p>
        <ul>
            <li>immediately after you add a new active domain, unless it is already in the KeyChest database; and</li>
            <li>at regular intervals, as defined in the User Guide (up to 48 hours).</li>
        </ul>
    </help-modal>
</template>

<script>
</script>

<style scoped>
</style>
