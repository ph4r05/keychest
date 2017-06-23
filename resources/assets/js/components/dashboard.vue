<template>
    <div class="dashboard-wrapper">
        <div class="alert alert-danger scan-alert" id="search-error" style="display: none">
            <strong>Error!</strong> <span id="error-text"></span>
        </div>

        <div class="alert alert-info alert-waiting scan-alert" id="search-info"
             v-if="loadingState == 0">
            <span>Loading data, please wait...</span>
        </div>

        <div class="alert alert-success scan-alert" id="search-success" style="display: none">
            <strong>Success!</strong> Scan finished.
        </div>

        <transition name="fade" v-on:after-leave="transition_hook">

        </transition>
    </div>

</template>

<script>
    import axios from 'axios';
    export default {
        data: function() {
            return {
                loadingState: 0,
                results: null,

                Req: window.Req,
                Laravel: window.Laravel
            };
        },

        mounted() {
            this.$nextTick(function () {
                this.hookup();
            })
        },

        computed: {
            hasAccount(){
                return !this.Laravel.authGuest;
            },
        },

        methods: {
            take(x, len){
                return _.take(x, len);
            },

            len(x) {
                if (x){
                    return x.length;
                }
                return 0;
            },

            transition_hook(el){
                this.recomp();
            },

            recomp(){
                this.$emit('onRecompNeeded');
            },

            hookup(){
                setTimeout(this.loadData, 0);
            },

            errMsg(msg) {
                $('#error-text').text(msg);
                this.formBlock(false);
                this.resultsLoaded = false;

                $('#search-info').hide();
                $('#search-error').show();
                this.recomp();
                this.$emit('onError', msg);
            },

            loadData(){
                const onFail = (function(){
                    this.loadingState = -1;
                    toastr.error('Error while loading, please, try again later', 'Error');
                }).bind(this);

                const onSuccess = (function(data){
                    this.loadingState = 1;
                    this.results = data;
                    setTimeout(this.processData, 0);
                }).bind(this);

                this.loadingState = 0;
                axios.get('/home/dashboard/data')
                    .then(response => {
                        if (!response || !response.data) {
                            onFail();
                        } else if (response.data['status'] === 'success') {
                            onSuccess(response.data);
                        } else {
                            onFail();
                        }
                    })
                    .catch(e => {
                        console.log("Add server failed: " + e);
                        onFail();
                    });
            },

            processData(){
                console.log('process data now...');
            },

            processResults() {
                const curTime = new Date().getTime() / 1000.0;
                for(const certId in this.results.certificates){
                    const cert = this.results.certificates[certId];
                    cert.valid_to_days = Math.round(10 * (cert.valid_to_utc - curTime) / 3600.0 / 24.0) / 10;
                    cert.valid_from_days = Math.round(10 * (curTime - cert.valid_from_utc) / 3600.0 / 24.0) / 10;
                    cert.matched_name = cert.matched_alt_names && cert.matched_alt_names.length > 0 ? cert.matched_alt_names[0] : cert.cname;
                }
            },

            postprocessResults(){
                if (!this.curJob){
                    this.errMsg('Scan not found');
                    return;
                }

                this.curJob.portString = this.curJob.port === 443 ? '' : ':' + this.curJob.port;
                this.curUrl = Req.buildUrl(this.curJob.scan_scheme, this.curJob.scan_host, this.curJob.port);

                if (!this.tlsScanError && !this.tlsScanHostCert){
                    this.errMsg('Could not detect host certificate');
                    return;
                }

                if (!this.tlsScan){
                    return;
                }

                // Downtime analysis
                if (this.results.downtimeTls){
                    this.downtimeWarning = !this.tlsScanError && this.tlsScanHostCert && this.tlsScan && this.tlsScanHostCert
                        && this.results.downtimeTls
                        && this.results.downtimeTls.count > 0
                        && this.results.downtimeTls.downtime > 0
                        && this.results.downtimeTls.gaps
                        && this.results.downtimeTls.gaps.length > 0
                        && this.results.downtimeTls.size > 0;
                }

                // Results validity
                if (this.tlsScanHostCert) {
                    if (this.tlsScanHostCert.is_expired) {
                        this.form.defcon = 1;
                        this.form.textStatus = 'ERROR';
                    } else {
                        if (this.tlsScanHostCert.valid_to_days < 2) {
                            this.form.defcon = 2;
                            this.form.textStatus = 'WARNING';
                        } else if (this.tlsScanHostCert.valid_to_days < 28) {
                            this.form.defcon = 3;
                            this.form.textStatus = 'PLAN';
                        } else {
                            this.form.defcon = 5;
                            this.form.textStatus = 'OK';
                        }
                    }
                }

                this.errTrusted = !this.tlsScanError && this.tlsScanHostCert && this.tlsScan
                    && !this.tlsScan.valid_trusted && !this.tlsScan.valid_path;

                this.errHostname = !this.tlsScanError && this.tlsScanHostCert && this.tlsScan
                    && !this.tlsScan.valid_trusted && this.tlsScan.valid_path && !this.tlsScan.valid_hostname;

                this.neighbourhood = this.tlsScanHostCert ? Req.neighbourDomainList(this.tlsScanHostCert.alt_names) : [];

                this.$emit('onResultsProcessed', this.results);
            },

            processTlsScan() {
                if (this.isTlsScanEmpty){
                    this.tlsScanError = true;
                    return;
                }

                this.tlsScan = this.results.tlsScans[0];
                if (this.tlsScan.follow_http_url){
                    const urlp = URL(this.tlsScan.follow_http_url, true);
                    if (!Req.isSameUrl(
                            'https', urlp.host, 443,
                            this.curJob.scan_scheme, this.curJob.scan_host, this.curJob.scan_port)) {
                        this.didYouMeanUrl = 'https://' + urlp.host;
                    }
                }

                if (!this.didYouMeanUrl && this.tlsScan.follow_https_url){
                    const urlp = URL(this.tlsScan.follow_https_url, true);
                    if (!Req.isSameUrl(
                            'https', urlp.host, 443,
                            this.curJob.scan_scheme, this.curJob.scan_host, this.curJob.scan_port)) {
                        this.didYouMeanUrl = 'https://' + urlp.host;
                    }
                }

                if (!this.tlsScan.certs_ids || this.tlsScan.status !== 1){
                    this.tlsScanError = true;
                    return;
                }

                if (this.tlsScan.cert_id_leaf in this.results.certificates) {
                    this.tlsScanLeafCert = this.results.certificates[this.tlsScan.cert_id_leaf];
                }

                if (this.tlsScanLeafCert){
                    this.tlsScanHostCert = this.tlsScanLeafCert;
                } else if (this.tlsScan.certs_ids && this.tlsScan.certs_ids.length === 1){
                    this.tlsScanHostCert = this.results.certificates[this.tlsScan.certs_ids[0]];
                }

                this.tlsScan.valid_trusted = this.tlsScan.valid_path && this.tlsScan.valid_hostname;
            },

            processCtScan(){
                if (!this.results.crtshScans || this.results.crtshScans.length === 0){
                    this.ctScanError = true;
                    return;
                }

                this.ctScan = this.results.crtshScans[0];

                let expiredCerts = [];
                let validCerts = [];

                for(let certId in this.results.certificates){
                    let cert = this.results.certificates[certId];
                    if (cert.is_ca){
                        continue;
                    }

                    if (cert.is_expired){
                        expiredCerts.push(cert);
                    } else {
                        validCerts.push(cert);
                    }
                }

                validCerts.sort((a, b) => a.valid_to_utc - b.valid_to_utc);
                expiredCerts.sort((a, b) => b.valid_to_utc - a.valid_to_utc);
                this.ctExpired = expiredCerts;
                this.ctValid = validCerts;
            },

            cleanResults(){
                this.results = null;
                this.loadingState = 0;
                this.$emit('onReset');
            },


        }
    }
</script>

<style>
    .scan-results-host {
        padding-left: 5px;
        padding-right: 5px;
    }

    .fade-enter-active, .fade-leave-active {
        transition: opacity 1.0s
    }
    .fade-enter, .fade-leave-to /* .fade-leave-active in <2.1.8 */ {
        opacity: 0
    }
</style>

