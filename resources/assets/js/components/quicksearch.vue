<template>
    <div class="container">
        <div class="row search">

            <div class="col-sm-8 col-sm-offset-2">
                <form role="form" method="get" id="search-form" @submit.prevent="submitForm()">
                    <!--{{ Laravel.csrfToken }}-->
                    <div class="input-group">
                        <input type="text" class="form-control input-sm" placeholder="Enter your domain to scan"
                               name="scan-target" id="scan-target" >
                        <span class="input-group-btn">
                            <button class="btn btn-default btn-sm" type="submit">
                                <span class="glyphicon glyphicon-search"></span>
                            </button>
                        </span>
                    </div>
                </form>

                <div class="alert alert-danger" id="search-error" style="display: none">
                    <strong>Error!</strong> <span id="error-text"></span>
                </div>

                <div class="alert alert-info alert-waiting" id="search-info" style="display: none">
                    <span id="info-text">Waiting for scan to finish...</span>
                </div>

                <div class="alert alert-success" id="search-success" style="display: none">
                    <strong>Success!</strong> Scan finished.
                </div>

                <transition name="fade">
                <div class="scan-results" id="scan-results" v-show="resultsLoaded">
                    <h1>Results for <span class="scan-results-host bg-success">{{ curJob.scan_host }}:{{ curJob.port }}</span></h1>

                    <div class="tls-results" id="tls-results">
                        <div class="alert alert-info" v-if="results && results.tlsScans.length == 0">
                            No TLS scan was performed
                        </div>

                        <div class="alert alert-warning" v-else-if="tlsScanError">
                            <strong>TLS Error</strong>: Could not scan {{ curJob.scan_host }} on port {{ curJob.port }}
                            <span v-if="tlsScan && tlsScan.err_code == 1"> (TLS handshake error)</span>
                            <span v-if="tlsScan && tlsScan.err_code == 2"> (Connection error)</span>
                            <span v-if="tlsScan && tlsScan.err_code == 3"> (Timeout)</span>.
                            <div v-if="tlsScan && tlsScan.follow_http_result == 'OK'">Did you mean {{ tlsScan.follow_http_url }} </div>
                        </div>

                        <div class="content" v-if="!tlsScanError">
                            <h3>Direct connect</h3>
                            <table class="table table-responsive">
                                <tbody>
                                <tr v-bind:class="{ success: tlsScan.valid_trusted, danger: !tlsScan.valid_trusted }">
                                    <th scope="row">Trusted</th>
                                    <td>{{ tlsScan.valid_trusted ? 'Yes' : 'No' }}</td>
                                </tr>
                                <tr v-if="tlsScanLeafCert !== null"
                                    v-bind:class="{
                                        success: !tlsScanLeafCert.is_expired && tlsScanLeafCert.valid_to_days >= 30,
                                        warning: !tlsScanLeafCert.is_expired && tlsScanLeafCert.valid_to_days < 30,
                                        danger: tlsScanLeafCert.is_expired }">
                                    <th scope="row">Validity</th>
                                    <td>{{ tlsScanLeafCert.valid_to }} ( {{ tlsScanLeafCert.valid_to_days }} days ) </td>
                                </tr>
                                </tbody>
                            </table>

                            <div class="alert alert-danger" v-if="!tlsScan.valid_trusted && !tlsScan.valid_path">
                                <p><strong>Error: </strong>The certificate is not valid</p>
                            </div>

                            <div class="alert alert-danger" v-if="!tlsScan.valid_trusted && tlsScan.valid_path
                            && !tlsScan.valid_hostname">
                                <p><strong>Error: </strong>The certificate is valid but the domain does not match</p>
                            </div>

                            <div class="alert alert-warning" v-if="tlsScanLeafCert && tlsScanLeafCert.is_le
                                && tlsScanLeafCert.valid_to_days<30.0 && tlsScanLeafCert.valid_to_days > 0">
                                <p><strong>Warning!</strong> This is a Let's Encrypt certificate but
                                the validity is less than 30 days.</p>

                                <p>In the correct setting this should not happen. Feel free to contact us for help.</p>
                            </div>

                            <h3>Certificate details</h3>
                            <table  class="table" v-if="tlsScanLeafCert !== null">
                                <tbody>
                                <tr>
                                    <th scope="row">Certificates in the chain</th>
                                    <td>{{ len(tlsScan.certs_ids) }}</td>
                                </tr>
                                <tr v-if="tlsScanLeafCert.is_le">
                                    <th scope="row">Let's Encrypt</th>
                                    <td>{{ tlsScanLeafCert.is_le ? 'Yes' : 'No' }}</td>
                                </tr>
                                <tr v-if="tlsScanLeafCert.is_cloudflare">
                                    <th scope="row" >Cloudflare</th>
                                    <td>{{ tlsScanLeafCert.is_cloudflare ? 'Yes' : 'No' }}</td>
                                </tr>
                                <tr v-bind:class="{danger: tlsScanLeafCert.is_expired }">
                                    <th scope="row">Time validity</th>
                                    <td>{{ tlsScanLeafCert.is_expired ? 'Expired' : 'Valid' }}</td>
                                </tr>
                                <tr >
                                    <th scope="row">Issued on</th>
                                    <td>{{ tlsScanLeafCert.valid_from }} ( {{ tlsScanLeafCert.valid_from_days }} days ago )</td>
                                </tr>
                                <tr v-bind:class="{danger: tlsScanLeafCert.is_expired }">
                                    <th scope="row">Valid to</th>
                                    <td>{{ tlsScanLeafCert.valid_to }} ( {{ tlsScanLeafCert.valid_to_days }} days ) </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>

                    <div class="ct-results" id="ct-results">
                        <h2>Certificate databases</h2>

                        <div v-if="ctValid.length > 0 || ctExpired.length > 0">
                        <h3>Active Certificates</h3>
                        <table class="table table-striped table-responsive" v-if="ctValid.length > 0">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Validity</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="cert in take(ctValid, 20)">
                                <td>{{ cert.matched_name }} </td>
                                <td>{{ cert.valid_to }} ( {{ cert.valid_to_days }} days ) </td>
                            </tr>
                            </tbody>
                        </table>
                        <p v-else>
                            No issued certificates found in databases.
                        </p>


                        <h3>Expired Certificates</h3>
                        <table class="table table-striped table-responsive" v-if="ctExpired.length > 0">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Expired</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="cert in take(ctExpired, 20)">
                                <td>{{ cert.matched_name }} </td>
                                <td>{{ cert.valid_to }} ( {{ -1*cert.valid_to_days }} days ago ) </td>
                            </tr>
                            </tbody>
                        </table>
                        <p v-else>
                            No expired certificates found in databases.
                        </p>
                        </div>
                        <p v-else>
                            No match found in certificate databases.
                        </p>

                    </div>

                </div>
                </transition>
            </div>

        </div>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                curUuid: null,
                curJob: {},
                resultsLoaded: false,
                results: null,

                tlsScan: {},
                tlsScanError: false,
                tlsScanLeafCert: null,

                ctScan: {},
                ctScanError: false,

                ctExpired: [],
                ctValid: [],

                Req: window.Req,
            };
        },

        mounted() {
            this.$nextTick(function () {
                this.hookup();
            })
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

            hookup(){

            },

            errMsg(msg) {
                $('#error-text').val(msg);

                $('#search-info').hide();
                $('#scan-results').hide();
                $('#search-error').show();
            },

            searchStarted() {
                // bodyProgress(true);
                $('#search-form').hide();
                $('#scan-results').hide();
                $('#search-info').show();
            },

            pollFinish() {
                getJobState(this.curUuid, (function(json){
                    console.log(json);

                    if (json.status !== 'success'){
                        this.errMsg('Job state fail, retry...');
                        setTimeout(this.pollFinish, 1000);
                        return;
                    }

                    this.curJob = json.job;
                    this.curJob.port = Req.defval(this.curJob.scan_port, 443);
                    if (this.curJob.state !== 'finished'){
                        setTimeout(this.pollFinish, 1000);
                    } else {
                        this.getResults();
                    }

                }).bind(this), (function(jqxhr, textStatus, error){
                    this.errMsg('Job failed');
                }).bind(this));
            },

            getResults() {
                getJobResult(this.curUuid, (function(json){
                    if (json.status !== 'success'){
                        this.errMsg('Job results fail, retry...');
                        setTimeout(this.getResults, 1000);
                        return;
                    }

                    this.showResults(json);

                }).bind(this), (function(jqxhr, textStatus, error){
                    this.errMsg('Could not get job results');
                }).bind(this));
            },

            showResults(json){
                this.results = json;
                // this.resultsLoaded = true;

                $('#search-info').hide();
                $('#search-success').show();
                setTimeout(()=>{
                    $('#search-success').hide('slow', ()=>{
                        this.resultsLoaded=true;
                        //$('#scan-results').show('slow');
                    });
                }, 250);

                // Process, show...
                this.processResults();
                this.processTlsScan();
                this.processCtScan();
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

            processTlsScan() {
                if (!this.results.tlsScans || this.results.tlsScans.length === 0){
                    this.tlsScanError = true;
                    return;
                }

                this.tlsScan = this.results.tlsScans[0];
                if (!this.tlsScan.certs_ids || this.tlsScan.status !== 1){
                    this.tlsScanError = true;
                    console.log('No TLS results');
                    return;
                }

                if (this.tlsScan.cert_id_leaf in this.results.certificates) {
                    this.tlsScanLeafCert = this.results.certificates[this.tlsScan.cert_id_leaf];
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

            submitForm(){
                let starget = $('#scan-target');
                let domain = starget.val();

                this.searchStarted();
                submitJob(domain, (function(json){
                    // bodyProgress(false);
                    if (json.status !== 'success'){
                        this.errMsg('Could not submit the scan');
                        return;
                    }

                    console.log(json);
                    this.curUuid = json.uuid;
                    setTimeout(this.pollFinish, 500);

                }).bind(this), (function(jqxhr, textStatus, error){
                    // bodyProgress(false);
                    this.errMsg(error);
                }).bind(this));
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

