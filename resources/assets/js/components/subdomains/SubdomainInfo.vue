<template>
    <!-- Subdomain watch modal info -->
    <div class="modal fade" id="subdomainsInfoModal" tabindex="-1" role="dialog"
         aria-labelledby="Domains for automated server discovery" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Domains for automated server discovery</h3>
                </div>

                <div class="modal-body">

                    <p>
                        Domains in this list are periodically scanned for sub-domains.
                        If auto-add feature is enabled newly detected sub-domains are automatically added to the
                        monitoring server list.
                    </p>

                    <p>
                        Technical note: Certificate Transparency logs scanning is used for detection.
                    </p>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
</script>

<style scoped>
</style>
