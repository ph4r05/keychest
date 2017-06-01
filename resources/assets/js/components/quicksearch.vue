<template>

    <div class="bloc bloc-fill-screen tc-onyx bgc-white l-bloc" id="intro" style="height: 854px;">
        <div class="container">
            <div class="row">

                <div class="col-sm-12">
                    <img src="/images/logo2-rgb_cropped.gif" class="img-responsive center-block" width="300">
                    <h3 class="text-center mg-lg hero-bloc-text-sub  tc-outer-space">
                        KeyChest - the free certificate planner
                    </h3>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">

                    <div class="col-sm-8 col-sm-offset-2">
                        <div class="form-group">
                            <form role="form" method="get" id="search-form" @submit.prevent="submitForm()">
                                <div class="input-group">
                                    <input type="text" class="form-control input"
                                           placeholder="Type your domain name, e.g., enigmabridge.com"
                                           name="scan-target" id="scan-target">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default btn" type="submit">
                                            <span class="glyphicon glyphicon-search"></span>
                                        </button>
                                    </span>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="row">
                        <div class="col-sm-6">
                            <a class="btn btn-lg pull-right btn-rich-electric-blue" id="btn-check-expire">Check expiry</a>
                        </div>
                        <div class="col-sm-6">
                            <a class="btn btn-lg pull-left btn-rich-electric-blue"
                               onclick="scrollToTarget('#register')">Create account</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row search">
                <div class="col-sm-8 col-sm-offset-2">
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
                                <span v-if="tlsScan && tlsScan.err_code == 1"> ( TLS handshake error )</span>
                                <span v-if="tlsScan && tlsScan.err_code == 2"> ( connection error )</span>
                                <span v-if="tlsScan && tlsScan.err_code == 3"> ( timeout )</span>.
                                <div v-if="didYouMeanUrl">Did you mean
                                    <a :href="didYouMeanUrlFull()">{{ didYouMeanUrl }}</a> ?</div>
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
                    <!-- End of results section -->

                </div>

            </div>

        </div>

        <div class="container fill-bloc-bottom-edge">
            <div class="row row-no-gutters">
                <div class="col-sm-12">
                    <a id="scroll-hero" class="blocs-hero-btn-dwn" href="https://keychest.net/#"><span class="fa fa-chevron-down"></span></a>
                </div>
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
                jobSubmittedNow: false,
                resultsLoaded: false,
                results: null,

                tlsScan: {},
                tlsScanError: false,
                tlsScanLeafCert: null,
                didYouMeanUrl: null,

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
                let uuid = Req.findGetParameter('uuid');
                let url = Req.findGetParameter('url');
                let new_job = Req.findGetParameter('new');

                if (url){
                    $('#scan-target').val(url);
                }

                if (uuid){
                    this.jobSubmittedNow = new_job;
                    this.onUuidProvided(uuid);
                }
            },

            didYouMeanUrlFull() {
                return '/scan?url=' + encodeURI(this.didYouMeanUrl);
            },

            errMsg(msg) {
                $('#error-text').text(msg);

                $('#search-info').hide();
                $('#scan-results').hide();
                $('#search-error').show();
            },

            searchStarted() {
                $('#search-form').hide();
                $('#scan-results').hide();
                $('#search-info').show();
            },

            onUuidProvided(uuid) {
                // UUID provided from the GET parameter:
                this.curUuid = uuid;
                this.searchStarted();
                if (!this.jobSubmittedNow){
                    $('#info-text').text('Loading scan results...');
                }
                setTimeout(this.pollFinish, 10);
            },

            pollFinish() {
                Req.getJobState(this.curUuid, (function(json){
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
                Req.getJobResult(this.curUuid, (function(json){
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

                $('#search-info').hide();
                if (this.jobSubmittedNow) {
                    $('#search-success').show();
                    setTimeout(() => {
                        $('#search-success').hide('slow', () => {
                            this.resultsLoaded = true;
                        });
                    }, 250);
                } else {
                    this.resultsLoaded = true;
                }

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
                if (this.tlsScan.follow_http_result === 'OK'){
                    const urlp = URL(this.tlsScan.follow_http_url, true);
                    this.didYouMeanUrl = 'https://' + urlp.host;
                }

                if (!this.tlsScan.certs_ids || this.tlsScan.status !== 1){
                    this.tlsScanError = true;
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
                Req.submitJob(domain, (function(json){
                    if (json.status !== 'success'){
                        this.errMsg('Could not submit the scan');
                        return;
                    }

                    console.log(json);

                    // Update URL so it contains params - job ID & url
                    let new_url = "/scan?uuid=" + json.uuid + '&url=' + encodeURI(domain);
                    try{
                        history.pushState(null, null, new_url); // new URL with history
                        history.replaceState(null, null, new_url); // replace the existing

                        this.curUuid = json.uuid;
                        this.jobSubmittedNow = true;
                        setTimeout(this.pollFinish, 500);

                    } catch(e) {
                        window.location.replace(new_url + '&new=1');
                    }

                }).bind(this), (function(jqxhr, textStatus, error){
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

