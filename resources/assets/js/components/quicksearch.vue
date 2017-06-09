<template>
    <div class="search-wrapper"
         v-bind:class="{'kc-search': searchEnabled, 'kc-loading': !searchEnabled && !resultsLoaded, 'kc-results': resultsLoaded}"
    >
        <div class="form-group">
            <form role="form" id="search-form" @submit.prevent="submitForm()">
                <div class="input-group" id="scan-wrapper">
                    <input type="text" class="form-control input"
                           placeholder="Type your domain name, e.g., enigmabridge.com"
                           name="scan-target" id="scan-target">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="submit">
                            <span class="glyphicon glyphicon-search"></span>
                        </button>
                    </span>
                </div>
            </form>
        </div>

        <div class="alert alert-danger scan-alert" id="search-error" style="display: none">
            <strong>Error!</strong> <span id="error-text"></span>
        </div>

        <div class="alert alert-info alert-waiting scan-alert" id="search-info" style="display: none">
            <span id="info-text">Waiting for scan to finish...</span>
        </div>

        <div class="alert alert-success scan-alert" id="search-success" style="display: none">
            <strong>Success!</strong> Scan finished.
        </div>

        <transition name="fade" v-on:after-leave="transition_hook">

        <!-- brief stats -->
        <div class="scan-results" id="scan-results-brief" v-show="resultsLoaded && !showExpertStats">

            <!-- No TLS scan -->
            <div class="alert alert-info" v-if="results && results.tlsScans.length == 0">
                No TLS scan was performed
            </div>

            <!-- TLS Error: problem with the scan -->
            <div class="alert alert-warning" v-else-if="tlsScanError">
                <strong>TLS Error</strong>: Could not scan <strong>{{ curJob.scan_host }}</strong> on port {{ curJob.port }}
                <span v-if="tlsScan && tlsScan.err_code == 1"> ( TLS handshake error )</span>
                <span v-if="tlsScan && tlsScan.err_code == 2"> ( connection error )</span>
                <span v-if="tlsScan && tlsScan.err_code == 3"> ( timeout )</span>
                <div v-if="didYouMeanUrl">Did you mean
                    <a :href="didYouMeanUrlFull()">{{ didYouMeanUrl }}</a> ?</div>
            </div>

            <!-- Brief results table -->
            <table class="table" v-if="results && results.tlsScans.length > 0
                                        && !tlsScanError && tlsScanLeafCert && tlsScan">
                <tbody>
                <tr v-bind:class="{
                                success: form.defcon==5,
                                warning: form.defcon<=4 && form.defcon>=2,
                                danger: form.defcon==1 }"
                >
                    <th>{{ curJob.scan_host }}{{ curJob.portString }}</th>
                    <td v-if="tlsScanLeafCert.is_expired">expired {{ Math.round((-1)*tlsScanLeafCert.valid_to_days) }} days</td>
                    <td v-else>expires in {{ Math.round(tlsScanLeafCert.valid_to_days) }} days</td>
                    <td> {{ form.textStatus }} </td>
                </tr>

                <tr v-bind:class="{
                                success: form.defcon==5,
                                warning: form.defcon<=4 && form.defcon>=2,
                                danger: form.defcon==1 }">

                    <td colspan="3" v-if="tlsScanLeafCert.is_expired && tlsScan.hsts_present">
                        Certificate expired. Your server is down (HSTS is set). Create an account to track or ask for help.</td>
                    <td colspan="3" v-else-if="tlsScanLeafCert.is_expired">
                        Certificate expired. Your server shows as "Not Secure". Create an account to track or ask for help.</td>
                    <td colspan="3" v-else-if="tlsScanLeafCert.valid_to_days<2">
                        The validity is less than 2 days. Renew now to avoid downtime! Create an account to track or ask for help.</td>
                    <td colspan="3" v-else-if="tlsScanLeafCert.valid_to_days<28">
                        The validity is less than 28 days. Plan renewal now! Create an account or ask for help.</td>
                    <td colspan="3" v-else-if="tlsScan.hsts_present">
                        There is nothing to do. Well done! Our compliments for using HSTS. Start tracking to avoid unavailability.</td>
                    <td colspan="3" v-else>
                        There is nothing to do. Well done! Create an account to stay on top of your certs.</td>
                </tr>

                </tbody>
            </table>

            <!-- Aux errors -->
            <div class="alert alert-danger" v-if="errTrusted">
                <p><strong>Error: </strong>The certificate is not trusted.
                <span v-if="tlsScan.certs_ids.length == 1">There is only a leaf certificate in the chain.</span></p>
            </div>

            <div class="alert alert-danger" v-if="errHostname">
                <p><strong>Error: </strong>The certificate is valid but the domain does not match.</p>
                <div v-if="neighbourhood.length > 0"> Certificate domains:
                    <ul class="domain-neighbours">
                        <li v-for="domain in neighbourhood">{{ domain }}</li>
                    </ul>
                </div>
            </div>

            <div class="alert alert-warning" v-if="downtimeWarning && results.downtimeTls.downtime > 60">
                <p><strong>Warning!</strong>
                    We detected only {{ Math.round(100 * (100 - (100.0 * results.downtimeTls.downtime / results.downtimeTls.size))) / 100.0 }} %
                    uptime. You were "not secure" for {{ Math.round(results.downtimeTls.downtime / 3600.0) }}
                    hours<span v-if="results.downtimeTls.downtime > 3600*24*3">
                         ({{ Math.round(results.downtimeTls.downtime / 24.0 / 3600.0) }} days)</span>.
                    Start tracking now.
                    </p>
            </div>

            <div class="alert alert-warning" v-if="false && !tlsScanError && tlsScanLeafCert && tlsScan && tlsScanLeafCert && tlsScanLeafCert.is_le
                        && tlsScanLeafCert.valid_to_days<30.0 && tlsScanLeafCert.valid_to_days > 0">
                <p><strong>Warning!</strong> This is a Let's Encrypt certificate but
                    the validity is less than 30 days.</p>

                <p>In the correct setting this should not happen. Feel free to contact us for help.</p>
            </div>

            <div class="alert alert-info" v-if="tlsScanLeafCert && !errHostname && neighbourhood.length > 2">
                <p>Here are domains from your neighbourhood:</p>
                <ul class="domain-neighbours">
                    <li v-for="domain in neighbourhood">{{ domain }}</li>
                </ul>
            </div>

        </div>
        </transition>
        <!-- End of brief stats -->

        <!-- Expert stats - will be shown later, on icon click -->
        <transition name="fade" v-on:after-leave="transition_hook">
        <div class="scan-results" id="scan-results" v-show="resultsLoaded && showExpertStats">
            <h1>Results for <span class="scan-results-host bg-success">{{ curJob.scan_host }}:{{ curJob.port }}</span></h1>

            <div class="tls-results" id="tls-results">
                <div class="alert alert-info" v-if="results && results.tlsScans.length == 0">
                    No TLS scan was performed
                </div>

                <div class="alert alert-warning" v-else-if="tlsScanError">
                    <strong>TLS Error</strong>: Could not scan <strong>{{ curJob.scan_host }}</strong> on port {{ curJob.port }}
                    <span v-if="tlsScan && tlsScan.err_code == 1"> ( TLS handshake error )</span>
                    <span v-if="tlsScan && tlsScan.err_code == 2"> ( connection error )</span>
                    <span v-if="tlsScan && tlsScan.err_code == 3"> ( timeout )</span>
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
                                success: !tlsScanLeafCert.is_expired && tlsScanLeafCert.valid_to_days >= 28,
                                warning: !tlsScanLeafCert.is_expired && tlsScanLeafCert.valid_to_days < 28,
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
                    <table class="table" v-if="tlsScanLeafCert !== null">
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
                <!-- End of expert stats -->

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

</template>

<script>
    export default {
        data: function() {
            return {
                curUuid: null,
                curJob: {},
                form: {
                    defcon: 5,
                    textStatus: 'OK'
                },
                jobSubmittedNow: false,
                resultsLoaded: false,
                results: null,
                searchEnabled: true,
                showExpertStats: false,

                tlsScan: {},
                tlsScanError: false,
                tlsScanLeafCert: null,
                didYouMeanUrl: null,
                downtimeWarning: false,
                neighbourhood: [],
                errTrusted: false,
                errHostname: false,

                ctScan: {},
                ctScanError: false,

                ctExpired: [],
                ctValid: [],

                Req: window.Req,
                Laravel: window.Laravel
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

            transition_hook(el){
                this.recomp();
            },

            recomp(){
                this.$emit('onRecompNeeded');
            },

            hookup(){
                let uuid = Req.findGetParameter('uuid');
                let url = Req.findGetParameter('url');
                let new_job = Req.findGetParameter('new');
                let scanTarget = $('#scan-target');

                // lowercase input
                $.fn.lowercaseFilter = function() {
                    $(this).css('text-transform', 'lowercase').bind('blur change', function(){
                        this.value = this.value.toLowerCase();
                    });
                };
                scanTarget.lowercaseFilter();

                if (url){
                    scanTarget.val(url);
                }

                if (uuid){
                    this.jobSubmittedNow = new_job;
                    this.onUuidProvided(uuid);
                } else if (url){
                    setTimeout(this.submitForm, 550); // auto submit after page load
                }
            },

            didYouMeanUrlFull() {
                return '/scan?url=' + encodeURI(this.didYouMeanUrl);
            },

            formBlock(block){
                // $('#search-form').hide();
                $("#search-form").find(":input").attr("disabled", block);
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

            searchStarted() {
                this.searchEnabled = false;
                this.formBlock(true);
                this.resultsLoaded = false;

                $('#search-error').hide();
                $('#search-info').show();
                this.recomp();
                this.$emit('onSearchStart');
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
                            this.$emit('onResultsLoaded', true);
                            this.recomp();
                        });
                    }, 250);
                } else {
                    this.resultsLoaded = true;
                    this.$emit('onResultsLoaded', true);
                }

                this.formBlock(false);
                this.recomp();

                // Process, show...
                this.processResults();
                this.processTlsScan();
                this.processCtScan();
                this.postprocessResults();
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
                this.curJob.portString = this.curJob.port === 443 ? '' : ':' + this.curJob.port;
                if (!this.tlsScanLeafCert || !this.tlsScan){
                    return;
                }

                // Downtime analysis
                if (this.results.downtimeTls){
                    this.downtimeWarning = !this.tlsScanError && this.tlsScanLeafCert && this.tlsScan && this.tlsScanLeafCert
                        && this.results.downtimeTls
                        && this.results.downtimeTls.count > 0
                        && this.results.downtimeTls.downtime > 0
                        && this.results.downtimeTls.gaps
                        && this.results.downtimeTls.gaps.length > 0
                        && this.results.downtimeTls.size > 0;
                }

                // Results validity
                if (this.tlsScanLeafCert.is_expired){
                    this.form.defcon = 1;
                    this.form.textStatus = 'ERROR';
                } else {
                    if (this.tlsScanLeafCert.valid_to_days < 2){
                        this.form.defcon = 2;
                        this.form.textStatus = 'WARNING';
                    } else if (this.tlsScanLeafCert.valid_to_days < 28){
                        this.form.defcon = 3;
                        this.form.textStatus = 'PLAN';
                    } else {
                        this.form.defcon = 5;
                        this.form.textStatus = 'OK';
                    }
                }

                this.errTrusted = !this.tlsScanError && this.tlsScanLeafCert && this.tlsScan
                    && !this.tlsScan.valid_trusted && !this.tlsScan.valid_path;

                this.errHostname = !this.tlsScanError && this.tlsScanLeafCert && this.tlsScan
                    && !this.tlsScan.valid_trusted && this.tlsScan.valid_path && !this.tlsScan.valid_hostname;

                // Neighbourhood
                if (this.tlsScanLeafCert){
                    this.neighbourhood = Req.neighbourDomainList(this.tlsScanLeafCert.alt_names);
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
                    if (!Req.isSameUrl('https', urlp.host, 443, this.curJob.scan_scheme, this.curJob.scan_host, this.curJob.scan_port)) {
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
                this.curUuid = {};
                this.curJob = {};
                this.form.defcon = 5;
                this.form.textStatus = 'OK';
                this.jobSubmittedNow = false;
                this.resultsLoaded = false;
                this.results = null;
                this.showExpertStats = false;

                this.tlsScan = {};
                this.tlsScanError = false;
                this.tlsScanLeafCert = null;
                this.didYouMeanUrl = null;
                this.neighbourhood = [];
                this.downtimeWarning = false;
                this.errTrusted = false;
                this.errHostname = false;

                this.ctScan = {};
                this.ctScanError = false;

                this.ctExpired = [];
                this.ctValid= [];
                this.$emit('onReset');
            },

            submitForm(){
                let starget = $('#scan-target');
                let domain = starget.val();

                // Minor domain validation.
                if (_.isEmpty(domain) || domain.split('.').length <= 1){
                    $( "#search-form" ).effect( "shake" );
                    toastr.error('Please enter correct domain.', 'Invalid input', {timeOut: 2000});
                    return;
                }

                this.searchStarted();
                this.cleanResults();
                Req.submitJob(domain, (function(json){
                    if (json.status !== 'success'){
                        this.errMsg('Could not submit the scan');
                        return;
                    }

                    console.log(json);

                    // Update URL so it contains params - job ID & url
                    let new_url = window.location.pathname + "?uuid=" + json.uuid + '&url=' + encodeURI(targetUri);
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

