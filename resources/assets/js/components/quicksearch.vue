<template>
    <div class="search-wrapper"
         v-bind:class="{'kc-search': searchEnabled, 'kc-loading': !searchEnabled && !resultsLoaded, 'kc-results': resultsLoaded}"
    >

        <!-- input form -->
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
        <!-- end of input form -->

        <div class="alert alert-danger scan-alert" id="search-error" style="display: none">
            <strong>Error!</strong> <span id="error-text"></span>
        </div>

        <div class="alert alert-info alert-waiting scan-alert" id="search-info" style="display: none">
            <span v-if="jobSubmittedNow">Waiting for scan to finish...</span>
            <span v-else="">Loading scan results...</span>
        </div>

        <div class="alert alert-success scan-alert" id="search-success" style="display: none">
            <strong>Success!</strong> Scan finished.
        </div>

        <transition name="fade" v-on:after-leave="transition_hook">

        <!-- brief stats -->
        <div class="scan-results" id="scan-results-brief" v-show="resultsLoaded && !showExpertStats">

            <!-- No TLS scan - probably invalid domain -->
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

            <!-- Label for loaded test (performed previously) -->
            <h3 class="loaded-test-label" v-if="showResultsTable && !jobSubmittedNow && curJob"
            >The scan was performed {{ new Date(curJob.created_at_utc * 1000).toLocaleString() }}</h3>

            <!-- Brief results table -->
            <table class="table" v-if="showResultsTable">
                <tbody>
                <tr v-bind:class="defconStyle">
                    <th>{{ curJob.scan_host }}{{ curJob.portString }}</th>
                    <td v-if="tlsScanHostCert.is_expired">expired {{ Math.round((-1)*tlsScanHostCert.valid_to_days) }} days</td>
                    <td v-else>expires in {{ Math.round(tlsScanHostCert.valid_to_days) }} days</td>
                    <td> {{ form.textStatus }} </td>
                </tr>

                <!-- Overal validity status, else-ifs from the most urgent to least -->
                <tr v-bind:class="defconStyle">
                    <td colspan="3" v-if="tlsScanHostCert.is_expired && tlsScan.hsts_present">
                        Certificate expired. Your server is down (HSTS is set). Create an account to track or ask for help.</td>
                    <td colspan="3" v-else-if="tlsScanHostCert.is_expired">
                        Certificate expired. Your server shows as "Not Secure". Create an account to track or ask for help.</td>
                    <td colspan="3" v-else-if="tlsScanHostCert.valid_to_days<2">
                        The validity is less than 2 days. Renew now to avoid downtime! Create an account to track or ask for help.</td>
                    <td colspan="3" v-else-if="tlsScanHostCert.valid_to_days<28">
                        The validity is less than 28 days. Plan renewal now! Create an account or ask for help.</td>
                    <td colspan="3" v-else-if="tlsScan.hsts_present">
                        There is nothing to do. Well done! Our compliments for using HSTS. <span v-if="!isMonitored">Start tracking to avoid unavailability.</span></td>
                    <td colspan="3" v-else>
                        There is nothing to do. Well done! <span v-if="!isMonitored">Start tracking to stay on top of your certs.</span></td>
                </tr>

                </tbody>
            </table>

            <!-- Start tracking -->
            <transition name="fade" v-on:after-leave="transition_hook">
            <div v-if="showTrackingButton" id="start-tracking-wrapper">
                <div class="form-group start-tracking">
                    <a v-if="Laravel.authGuest"
                       class="btn btn-primary btn-block btn-lg"
                       href="/register">Start tracking</a
                    ><a id="start-tracking-button" v-else=""
                       class="btn btn-primary btn-block btn-lg"
                       v-bind:disabled="addingStatus==2"
                       v-on:click.stop="startTracking"
                    >Start tracking</a>
                </div>
            </div>
            </transition>
            <!-- end of start tracking -->

            <!-- Aux errors -->
            <!-- Cert not trusted -->
            <div class="alert alert-danger" v-if="errTrusted">
                <div v-if="tlsScanHostCert && tlsScanHostCert.is_self_signed">
                    We detected an untrusted self-signed certificate. Please get in touch, if you want to track your own certificates.
                </div>
                <div v-else-if="tlsScanHostCert && tlsScanHostCert.is_ca">
                    We detected an untrusted certificate. Please get in touch, if you want to track your own certificates.
                </div>
                <div v-else-if="tlsScanHostCert && tlsScan.certs_ids.length > 1">
                    We detected an untrusted certificate chain. Please get in touch, if you want to track your own certificates.
                </div>
                <div v-else-if="tlsScanHostCert">
                    We detected an untrusted certificate. Please get in touch, if you want to track your own certificates.
                </div>
                <div v-else="">
                    <strong>Error: </strong>The certificate is not trusted. <span
                            v-if="tlsScan.certs_ids.length === 0">No certificate was found.</span><span
                            v-else-if="!tlsScanHostCert">Host certificate could not be detected.</span><span
                            v-else-if="tlsScan.certs_ids.length === 1 && tlsScanLeafCert">There is only a leaf certificate in the chain (probably missing intermediate?).</span><!--
                    -->
                </div>
            </div>

            <!-- Hostname mismatch -->
            <div class="alert alert-danger" v-if="errHostname">
                <p><strong>Error: </strong>The certificate is valid but the domain does not match.</p>
                <div v-if="neighbourhood.length > 0"> Certificate domains:
                    <ul class="domain-neighbours">
                        <li v-for="domain in neighbourhood">
                            <a v-bind:href="'?url=' + encodeURI(Req.removeWildcard(domain))">{{ domain }}</a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Downtime -->
            <div class="alert alert-warning" v-if="downtimeWarning && results.downtimeTls.downtime > 60">
                <p><strong>Warning!</strong>
                    We detected only {{ Math.round(100 * (100 - (100.0 * results.downtimeTls.downtime / results.downtimeTls.size))) / 100.0 }} %
                    uptime. You were "not secure" for {{ Math.round(results.downtimeTls.downtime / 3600.0) }}
                    hours<span v-if="results.downtimeTls.downtime > 3600*24*3">
                         ({{ Math.round(results.downtimeTls.downtime / 24.0 / 3600.0) }} days)</span>.
                    <span v-if="!isMonitored">Start tracking now.</span>
                    </p>
            </div>

            <!-- LE validity < 30 days (disabled temporarily) -->
            <div class="alert alert-warning" v-if="false && showLeWarning">
                <p><strong>Warning!</strong> This is a Let's Encrypt certificate but
                    the validity is less than 30 days.</p>

                <p>In the correct setting this should not happen. Feel free to contact us for help.</p>
            </div>

            <!-- Redirect - scan that too? -->
            <div class="alert alert-info" v-if="!tlsScanError && didYouMeanUrl">
                Do you also want to check redirected domain <a :href="didYouMeanUrlFull()">{{ didYouMeanUrl }}</a> ?
            </div>

            <!-- Neighbours -->
            <div class="alert alert-info" v-if="tlsScanHostCert && !errHostname && neighbourhood.length > 2">
                <p>Here are domains from your neighborhood:</p>
                <ul class="domain-neighbours">
                    <li v-for="domain in neighbourhood">
                        <span v-if="!Req.isWildcard(domain)"><a v-bind:href="'?url=' + encodeURI(domain)">{{ domain }}</a></span
                        ><span v-else="">{{ domain }}</span
                    ></li>
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
                        <tr v-if="tlsScanHostCert !== null"
                            v-bind:class="{
                                success: !tlsScanHostCert.is_expired && tlsScanHostCert.valid_to_days >= 28,
                                warning: !tlsScanHostCert.is_expired && tlsScanHostCert.valid_to_days < 28,
                                danger: tlsScanHostCert.is_expired }">
                            <th scope="row">Validity</th>
                            <td>{{ tlsScanHostCert.valid_to }} ( {{ tlsScanHostCert.valid_to_days }} days ) </td>
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

                    <div class="alert alert-warning" v-if="tlsScanHostCert && tlsScanHostCert.is_le
                        && tlsScanHostCert.valid_to_days<30.0 && tlsScanHostCert.valid_to_days > 0">
                        <p><strong>Warning!</strong> This is a Let's Encrypt certificate but
                        the validity is less than 30 days.</p>

                        <p>In the correct setting this should not happen. Feel free to contact us for help.</p>
                    </div>

                    <h3>Certificate details</h3>
                    <table class="table" v-if="tlsScanHostCert !== null">
                        <tbody>
                        <tr>
                            <th scope="row">Certificates in the chain</th>
                            <td>{{ len(tlsScan.certs_ids) }}</td>
                        </tr>
                        <tr v-if="tlsScanHostCert.is_le">
                            <th scope="row">Let's Encrypt</th>
                            <td>{{ tlsScanHostCert.is_le ? 'Yes' : 'No' }}</td>
                        </tr>
                        <tr v-if="tlsScanHostCert.is_cloudflare">
                            <th scope="row" >Cloudflare</th>
                            <td>{{ tlsScanHostCert.is_cloudflare ? 'Yes' : 'No' }}</td>
                        </tr>
                        <tr v-bind:class="{danger: tlsScanHostCert.is_expired }">
                            <th scope="row">Time validity</th>
                            <td>{{ tlsScanHostCert.is_expired ? 'Expired' : 'Valid' }}</td>
                        </tr>
                        <tr >
                            <th scope="row">Issued on</th>
                            <td>{{ tlsScanHostCert.valid_from }} ( {{ tlsScanHostCert.valid_from_days }} days ago )</td>
                        </tr>
                        <tr v-bind:class="{danger: tlsScanHostCert.is_expired }">
                            <th scope="row">Valid to</th>
                            <td>{{ tlsScanHostCert.valid_to }} ( {{ tlsScanHostCert.valid_to_days }} days ) </td>
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
    import axios from 'axios';
    export default {
        data: function() {
            return {
                curUuid: null,
                curJob: {},
                curUrl: null,
                form: {
                    defcon: 5,
                    textStatus: 'OK'
                },
                jobSubmittedNow: false,
                resultsLoaded: false,
                results: null,
                searchEnabled: true,
                showExpertStats: false,
                addingStatus: 0,

                tlsScan: {},
                tlsScanError: false,
                tlsScanLeafCert: null,
                tlsScanHostCert: null,
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

        computed: {
            isMonitored(){
                return (this.results && this.results.isMonitored) || this.addingStatus === 1;
            },

            hasAccount(){
                return !this.Laravel.authGuest;
            },

            showResultsTable(){
                return this.results && this.results.tlsScans.length > 0
                    && !this.tlsScanError && this.tlsScanHostCert && this.tlsScan;
            },

            showTrackingButton(){
                return this.results && this.results.canAddToList && this.addingStatus!==3 && this.addingStatus!==1;
            },

            showLeWarning(){
                return !this.tlsScanError && this.tlsScanHostCert && this.tlsScan
                    && this.tlsScanHostCert && this.tlsScanHostCert.is_le
                    && this.tlsScanHostCert.valid_to_days<30.0 && this.tlsScanHostCert.valid_to_days > 0;
            },

            defconStyle(){
                return {
                    success: this.form.defcon===5,
                    warning: this.form.defcon<=4 && this.form.defcon>=2,
                    danger: this.form.defcon===1 };
            }
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
                    this.curUrl = url;
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
                return '?url=' + encodeURI(this.didYouMeanUrl);
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

            searchStarted(data) {
                this.searchEnabled = false;
                this.formBlock(true);
                this.resultsLoaded = false;

                $('#search-error').hide();
                $('#search-info').show();
                this.recomp();
                this.$emit('onSearchStart', data);
            },

            onUuidProvided(uuid) {
                // UUID provided from the GET parameter:
                this.curUuid = uuid;
                this.searchStarted();
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
                    this.errMsg('Could not load scan with given ID');
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
                if (this.tlsScanHostCert.is_expired){
                    this.form.defcon = 1;
                    this.form.textStatus = 'ERROR';
                } else {
                    if (this.tlsScanHostCert.valid_to_days < 2){
                        this.form.defcon = 2;
                        this.form.textStatus = 'WARNING';
                    } else if (this.tlsScanHostCert.valid_to_days < 28){
                        this.form.defcon = 3;
                        this.form.textStatus = 'PLAN';
                    } else {
                        this.form.defcon = 5;
                        this.form.textStatus = 'OK';
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
                if (!this.results.tlsScans || this.results.tlsScans.length === 0){
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
                this.curUuid = {};
                this.curJob = {};
                this.form.defcon = 5;
                this.form.textStatus = 'OK';
                this.jobSubmittedNow = false;
                this.resultsLoaded = false;
                this.results = null;
                this.showExpertStats = false;
                this.addingStatus = 0;

                this.tlsScan = {};
                this.tlsScanError = false;
                this.tlsScanHostCert = null;
                this.tlsScanLeafCert = null;
                this.didYouMeanUrl = null;
                this.neighbourhood = [];
                this.downtimeWarning = false;
                this.errTrusted = false;
                this.errHostname = false;

                this.ctScan = {};
                this.ctScanError = false;

                this.ctExpired = [];
                this.ctValid = [];
                this.$emit('onReset');
            },

            submitForm(){
                let starget = $('#scan-target');
                let targetUri = starget.val();

                // Minor domain validation.
                if (_.isEmpty(targetUri) || targetUri.split('.').length <= 1){
                    $( "#search-form" ).effect( "shake" );
                    toastr.error('Please enter correct domain.', 'Invalid input', {timeOut: 2000});
                    return;
                }

                this.cleanResults();
                this.searchStarted({'host': targetUri});
                this.curUrl = targetUri;
                this.jobSubmittedNow = true;

                Req.submitJob(targetUri, (function(json){
                    if (json.status !== 'success'){
                        this.errMsg('Could not submit the scan');
                        return;
                    }

                    this.$emit('onjobSubmitted', json);

                    // Update URL so it contains params - job ID & url
                    let new_url = window.location.pathname + "?uuid=" + json.uuid
                        + '&url=' + encodeURI(targetUri);
                    try{
                        history.pushState(null, null, new_url); // new URL with history
                        history.replaceState(null, null, new_url); // replace the existing

                        this.curUuid = json.uuid;
                        setTimeout(this.pollFinish, 500);

                    } catch(e) {
                        window.location.replace(new_url + '&new=1');
                    }

                }).bind(this), (function(jqxhr, textStatus, error){
                    this.errMsg(error);
                }).bind(this));
            },

            startTracking(){
                const server2monitor = this.curUrl;

                // Minor domain validation.
                if (_.isEmpty(server2monitor) || server2monitor.split('.').length <= 1){
                    $('#start-tracking-wrapper').effect( "shake" );
                    toastr.error('Please enter correct domain.', 'Invalid input', {timeOut: 2000, preventDuplicates: true});
                    return;
                }

                Req.bodyProgress(true);

                const onFail = (function(){
                    Req.bodyProgress(false);
                    this.addingStatus = -1;
                    $('#start-tracking-wrapper').effect( "shake" );
                    toastr.error('Error while adding the server, please, try again later', 'Error');
                }).bind(this);

                const onDuplicate = (function(){
                    Req.bodyProgress(false);
                    this.addingStatus = 3;
                    toastr.success('This host is already being monitored.', 'Already present');
                }).bind(this);

                const onSuccess = (function(data){
                    Req.bodyProgress(false);
                    this.addingStatus = 1;
                    this.$emit('onServerAdded', data);
                    toastr.success('Server Added Successfully.', 'Success', {preventDuplicates: true});
                }).bind(this);

                this.addingStatus = 2;
                axios.post('/home/servers/add', {'server': server2monitor})
                    .then(response => {
                        if (!response || !response.data) {
                            onFail();
                        } else if (response.data['status'] === 'already-present'){
                            onDuplicate();
                        } else if (response.data['status'] === 'success') {
                            onSuccess(response.data);
                        } else {
                            onFail();
                        }
                    })
                    .catch(e => {
                        if (e && e.response && e.response.status === 410){
                            onDuplicate();
                        } else {
                            console.log("Add server failed: " + e);
                            onFail();
                        }
                    });
            }
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

