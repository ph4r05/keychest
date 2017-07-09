<template>
    <!-- Subdomain watch modal info -->
    <help-modal id="subdomainsInfoModal" title="Automatic server discovery">
        <p>
            Automatic server monitoring
            If you enable the "Watch Now" feature, KeyChest will automatically add all new servers
            to monitoring. They will appear in the dashboard with test results on port 443 (HTTPS).
        </p>
        <p>
            Note: Detection of servers in active domains happens:
        </p>
        <ul>
            <li>immediately after you add a new active domain, unless it is already in the KeyChest database; and</li>
            <li>subsequently in regular intervals, as defined in the User Guide (up to 48 hours).</li>
        </ul>
    </help-modal>
</template>

<script>
</script>

<style scoped>
</style>
