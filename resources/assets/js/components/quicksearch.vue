<template>
    <div class="search-wrapper"
         v-bind:class="{'kc-search': searchEnabled, 'kc-loading': !searchEnabled && !resultsLoaded, 'kc-results': resultsLoaded}"
    >

        <!-- input form -->
        <div class="form-group">
            <form role="form" id="search-form" @submit.prevent="submitForm()">
                <div class="input-group" id="scan-wrapper">
                    <input type="text" class="form-control input"
                           autocorrect="off" autocapitalize="off" spellcheck="false"
                           placeholder="Server name with optional port, e.g., keychest.net or keychest.net:465"
                           name="scan-target" id="scan-target">

                    <span class="input-group-btn">
                        <a class="btn btn-default" data-toggle="modal" data-target="#scanHelpModal" v-if="landing">
                            <span class="fa fa-question-circle"></span>
                        </a>

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
            <span v-if="jobSubmittedNow && !wsFinished">Waiting for spot check to finish ...</span>
            <span v-else-if="!jobSubmittedNow || wsFinished">Loading test results</span>
        </div>

        <div class="alert alert-default scan-alert" id="search-success" style="display: none">
            <strong>Success!</strong> Spot check finished.
        </div>

        <transition name="fade" v-on:after-leave="transition_hook">

        <!-- brief stats -->
        <div class="scan-results" id="scan-results-brief" v-show="resultsLoaded && !showExpertStats">

            <!-- DNS problem: do domain resolved -->
            <div class="alert alert-warning" v-if="hasDnsProblem && resultsLoaded">
                <strong>DNS error</strong>: We could not resolve the <strong>{{ curJob.scan_host }}</strong> domain.<br/>
                <span>Please make sure the server name is correct.</span>
            </div>

            <!-- No TLS scan - probably invalid domain -->
            <div class="alert alert-info" v-else-if="isTlsScanEmpty && resultsLoaded">
                No TLS scan could be performed for the <u>{{ curJob.scan_host }}</u><template
                    v-if="curJob.scan_ip"> at {{ curJob.scan_ip }}</template>.
            </div>

            <!-- UX - nice message for missing TLS -->
            <div class="alert alert-danger" v-else-if="tlsScanError && tlsScan && tlsScan.err_code == 2">
                There is no HTTPS/TLS server <u>{{ curJob.scan_host }}</u> at the network address
                <span v-if="scanIpIsIpv6">IPv6 {{ scanIp }}.<br>If {{ curJob.scan_host }} is correct, you may
                not be able to renew Let&#39;s Encrypt certificates, see more at <a target="_blank"
                 href="https://community.letsencrypt.org/t/preferring-ipv6-for-challenge-validation-of-dual-homed-hosts/34774">
                        letsencrypt.org</a>.</span>
                <span v-else>IPv4 {{ scanIp }}.</span>
            </div>

            <!-- TLS Error: problem with the scan -->
            <div class="alert alert-warning" v-else-if="tlsScanError">
                <strong>TLS Error</strong>: tests of <strong>{{ curJob.scan_host }}</strong> on port
                <strong>{{ curJob.port }}</strong> failed due to:
                <span v-if="tlsScan && tlsScan.err_code == 1"> TLS handshake Error</span>
                <span v-if="tlsScan && tlsScan.err_code == 2"> Connection Error</span>
                <span v-if="tlsScan && tlsScan.err_code == 3"> Timeout</span>
                <span v-if="tlsScan && tlsScan.err_code == 4"> Domain lookup error</span>
                <span v-if="tlsScan && tlsScan.err_code == 5"> No TLS/SSL server found</span>
                <div v-if="didYouMeanUrl">Test failed, can we use
                    <a :href="didYouMeanUrlFull()">{{ didYouMeanUrl }}</a> instead?</div>
            </div>

            <!-- Label for loaded test (performed previously) -->
            <h5 class="loaded-test-label" v-if="showResultsTable && !jobSubmittedNow && curJob"
            >We tested the server on {{ new Date(curJob.created_at_utc * 1000).toLocaleString() }}</h5>

            <!-- Brief results table -->
            <table class="table" v-if="showResultsTable">
                <tbody>
                <tr v-bind:class="defconStyle">
                    <td><strong v-if="scanIpIsIpv6"><span class="tc-dark-green">IPv6 {{scanIp}},</span> {{ curJob.scan_host }}{{ curJob.portString }}</strong>
                        <strong v-else><span class="tc-dark-green">IPv4 {{scanIp}},</span> {{ curJob.scan_host }}{{ curJob.portString }}</strong>
                        <span v-if="tlsScanHostCert && tlsScanHostCert.issuerOrgNorm"> (by {{ tlsScanHostCert.issuerOrgNorm }})</span></td>
                    <td v-if="tlsScanHostCert.is_expired">expired {{ Math.round((-1)*tlsScanHostCert.valid_to_days) }} days ago</td>
                    <td v-else>expires in {{ Math.round(tlsScanHostCert.valid_to_days) }} days</td>
                    <td> {{ form.textStatus }} </td>
                </tr>

                <!-- Overall validity status, else-ifs from the most urgent to least -->
                <tr v-bind:class="defconStyle">
                    <td colspan="3" v-if="tlsScanHostCert.is_expired && tlsScan.hsts_present">
                        Certificate expired. Your server is down and HSTS prevents all connections. Start watching to see
                        changes or get in touch for help.</td>
                    <td colspan="3" v-else-if="tlsScanHostCert.is_expired">
                        Certificate expired. Web browsers will show a warning page that the server is not secure and
                        potentially dangerous. Start watching to see changes or get in touch for help.</td>
                    <td colspan="3" v-else-if="tlsScanHostCert.valid_to_days<2">
                        Certificate expires in less than 2 days. Renew it now to avoid downtime! Start watching to see
                        changes or get in touch for help.</td>
                    <td colspan="3" v-else-if="tlsScanHostCert.valid_to_days<28">
                        Certificate expires in less than 28 days. Plan renewal now! Start watching to see
                        changes or get in touch for help.</td>
                    <td colspan="3" v-else-if="tlsScan.hsts_present">
                        All looks good, well done! Our compliments for using HSTS. <span v-if="!isMonitored">Start
                        tracking to avoid downtimes.</span></td>
                    <td colspan="3" v-else>
                        All looks good, well done! <span v-if="!isMonitored">Start watching to stay on top of your
                        certificates.</span></td>
                </tr>

                </tbody>
            </table>

            <!-- Start tracking -->
            <transition name="fade" v-on:after-leave="transition_hook">
            <div v-if="showTrackingButton" id="start-tracking-wrapper">
                <div class="form-group start-tracking">
                    <a v-if="Laravel.authGuest"
                       class="btn btn-primary btn-block btn-lg"
                       href="/register?start_watching=1">Start watching</a>
                    <a id="start-tracking-button" v-else=""
                       class="btn btn-primary btn-block btn-lg"
                       v-bind:disabled="addingStatus==2"
                       v-on:click.stop="startTracking"
                    >Start watching</a>
                </div>
            </div>
            </transition>
            <!-- end of start tracking -->

            <!-- Aux errors -->
            <!-- Cert not trusted -->
            <div class="alert alert-danger" v-if="errTrusted">
                <div v-if="tlsScanHostCert && tlsScanHostCert.is_self_signed">
                    The server sent a self-signed certificate. Check its configuration, re-test, and start watching
                    changes. Get in touch for help.
                </div>
                <div v-else-if="tlsScanHostCert && tlsScanHostCert.is_ca">
                    The server's certificate has a CA flag and can be used only for issuing other certificates, not
                    for server authentication. Check the server's configuration, re-test, and start watching changes.
                    Get in touch for help.
                </div>
                <div v-else-if="tlsScanHostCert && tlsScan.certs_ids.length > 1">
                    We couldn't verify the certificate chain sent by the server. Check the server's configuration, re-test, and start
                    watching changes. Get in touch for help.
                </div>
                <div v-else-if="tlsScanHostCert && tlsScan.certs_ids.length === 1">
                    The server sent only its own certificate, a certificate of the issuing CA is missing (aka bundle).
                    Check the server's configuration, re-test, and start watching changes. Get in touch for help.
                </div>
                <div v-else-if="tlsScanHostCert">
                    We couldn't verify the server as it didn't send any certificates. Check its configuration, re-test,
                    and start watching changes. Get in touch for help.
                </div>
                <div v-else="">
                    <strong>Error: </strong>Server is not trusted as <span
                            v-if="tlsScan.certs_ids.length === 0">it sent no certificate.</span><span
                            v-else-if="!tlsScanHostCert">we couldn't create secure connection (TLS handshake).</span><span
                            v-else-if="tlsScan.certs_ids.length === 1 && tlsScanLeafCert">it sent only its own certificate.
                    A certificate of the issuing CA is missing (aka bundle).</span><!--
                    -->
                </div>
            </div>

            <!-- Hostname mismatch -->
            <div class="alert alert-danger" v-if="errHostname">
                <p><strong>Error: </strong>The server sent a certificate, which doesn't contain the server's name. It may
                be flagged as an attack.</p>
                <div v-if="neighbourhood.length > 0"> Domain names in the certificate are:
                    <ul class="domain-neighbours">
                        <li v-for="domain in neighbourhood">
                            <a v-bind:href="newScanUrl(domain)">{{ domain }}</a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Whois - expiration -->
            <div class="alert" v-if="showWhoisWarning"
                 v-bind:class="{
                    'alert-danger': this.results.whois.expires_at_days <= 30,
                    'alert-warning': this.results.whois.expires_at_days > 30}" >
                <strong>Warning</strong>: domain <u>{{ results.whois.domain }}</u> expires in
                {{ Math.floor(results.whois.expires_at_days) }} days.
                Consider domain renewal. <span v-if="!isMonitored">Start watching now.</span>
            </div>

            <!-- Downtime -->
            <div class="alert alert-warning" v-if="downtimeWarning && results.downtimeTls.downtime > 60">
                <p><strong>Warning!</strong>
                    We detected only {{ Math.round(100 * (100 - (100.0 * results.downtimeTls.downtime / results.downtimeTls.size))) / 100.0 }} %
                    uptime. You were "not secure" for at least {{ Math.round(results.downtimeTls.downtime / 3600.0) }}
                    hours<span v-if="results.downtimeTls.downtime > 3600*24*3">
                         ({{ Math.round(results.downtimeTls.downtime / 24.0 / 3600.0) }} days)</span>.
                    <span v-if="!isMonitored">Start watching now.</span>
                    </p>
            </div>

            <!-- LE validity < 30 days (disabled temporarily) -->
            <div class="alert alert-warning" v-if="false && showLeWarning">
                <p><strong>Warning!</strong> This is a Let's Encrypt certificate but
                    the validity is less than 30 days.In the correct setting this should not happen.
                    Feel free to contact us for help.</p>
            </div>

            <!-- Redirect - scan that too? -->
            <div class="alert alert-info" v-if="!tlsScanError && didYouMeanUrl">
                We detected a redirection. Click to check destination server <a :href="didYouMeanUrlFull()">{{ didYouMeanUrl }}</a>.
            </div>

            <!-- Other IP addresses -->
            <div class="alert alert-info" v-if="!hasDnsProblem && scanIp && ips.length > 1">
                We have found additional network {{ pluralize('address', anotherIps.length) }} for {{ curJob.scan_host }}.
                Please click on a particular address to test it.<br>
                <span v-for="ip in anotherIps"> &nbsp;<a v-bind:href="newScanUrl(null, ip.ip)">{{ ip.ip }}</a></span>.
            </div>

            <!-- Neighbours -->
            <div class="alert alert-info" v-if="tlsScanHostCert && !errHostname && neighbourhood.length > 2">
                <p>The server sent a multi-name (SAN) certificate. It means that all of the following servers are
                    allowed to use the same private key:</p>
                <ul class="domain-neighbours">
                    <li v-for="domain in neighbourhood">
                        <span v-if="!Req.isWildcard(domain)"><a v-bind:href="newScanUrl(domain)">{{ domain }}</a></span
                        ><span v-else="">{{ domain }}</span
                    ></li>
                </ul>
            </div>

        </div>
        </transition>
        <!-- End of brief stats -->

        <!-- Expert stats - will be shown later, on icon click -->
        <transition name="fade" v-on:after-leave="transition_hook">
        <div class="scan-results" id="scan-results" v-if="resultsLoaded && showExpertStats">
            <h1>Results for <span class="scan-results-host bg-success">{{ curJob.scan_host }}:{{ curJob.port }}</span></h1>

            <div class="tls-results" id="tls-results">
                <div class="alert alert-info" v-if="isTlsScanEmpty">
                    No certificate tests were completed
                </div>

                <div class="alert alert-warning" v-else-if="tlsScanError">
                    <strong>TLS Error</strong>: Certificate tests <strong>{{ curJob.scan_host }}</strong> on port
                    {{ curJob.port }} failed due to
                    <span v-if="tlsScan && tlsScan.err_code == 1"> TLS Handshake Error</span>
                    <span v-if="tlsScan && tlsScan.err_code == 2"> Connection Error</span>
                    <span v-if="tlsScan && tlsScan.err_code == 3"> Timeout</span>
                    <span v-if="tlsScan && tlsScan.err_code == 4"> Domain lookup error</span>
                    <span v-if="tlsScan && tlsScan.err_code == 5"> No TLS/SSL server found</span>
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
                        <p><strong>Error: </strong>The certificate is not valid.</p>
                    </div>

                    <div class="alert alert-danger" v-if="!tlsScan.valid_trusted && tlsScan.valid_path
                    && !tlsScan.valid_hostname">
                        <p><strong>Error: </strong>Server name is not in the server's certificate.</p>
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
                            <th scope="row">The number of certificates in the trust chain is</th>
                            <td>{{ len(tlsScan.certs_ids) }}</td>
                        </tr>
                        <tr v-if="tlsScanHostCert.is_le">
                            <th scope="row">Free Let's Encrypt</th>
                            <td>{{ tlsScanHostCert.is_le ? 'Yes' : 'No' }}</td>
                        </tr>
                        <tr v-if="tlsScanHostCert.is_cloudflare">
                            <th scope="row" >Certificate managed by CDN or ISP</th>
                            <td>{{ tlsScanHostCert.is_cloudflare ? 'Yes' : 'No' }}</td>
                        </tr>
                        <tr v-bind:class="{danger: tlsScanHostCert.is_expired }">
                            <th scope="row">Time validity</th>
                            <td>{{ tlsScanHostCert.is_expired ? 'Expired' : 'OK' }}</td>
                        </tr>
                        <tr >
                            <th scope="row">Issued on</th>
                            <td>{{ tlsScanHostCert.valid_from }} ({{ tlsScanHostCert.valid_from_days }} days ago)</td>
                        </tr>
                        <tr v-bind:class="{danger: tlsScanHostCert.is_expired }">
                            <th scope="row">Expires on</th>
                            <td>{{ tlsScanHostCert.valid_to }} (in {{ tlsScanHostCert.valid_to_days }} days) </td>
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
                    No issued certificates found in certificate transparency (CT) databases.
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
                        <td>{{ cert.valid_to }} ({{ -1*cert.valid_to_days }} days ago) </td>
                    </tr>
                    </tbody>
                </table>
                <p v-else>
                    No expired certificates found in certificate transparency (CT) databases.
                </p>
                </div>
                <p v-else>
                    No match found in certificate transparency (CT) databases.
                </p>

            </div>

        </div>
        </transition>
        <!-- End of results section -->
    </div>

</template>

<script>
    import axios from 'axios';
    import moment from 'moment';
    import pluralize from 'pluralize';

    export default {
        props: {
            landing: {
                required: false,
                type: Boolean,
                default: false
            },
        },

        data: function() {
            return {
                curUuid: null,
                curJob: {},
                curUrl: null,
                curIp: null,
                curChannel: null,
                form: {
                    defcon: 5,
                    textStatus: 'OK'
                },
                jobSubmittedNow: false,
                resultsLoaded: false,
                wsStarted: false,
                wsFinished: false,
                results: { },
                searchEnabled: true,
                showExpertStats: false,
                addingStatus: 0,

                tlsScan: {
                    ip_scanned: null
                },
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

                pollTimer: null,
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

            isTlsScanEmpty(){
                return !this.results || !this.results.tlsScans || this.results.tlsScans.length === 0;
            },

            showResultsTable(){
                try {
                    return this.results
                        && this.results.tlsScans
                        && this.results.tlsScans.length > 0
                        && !this.tlsScanError
                        && this.tlsScanHostCert
                        && this.tlsScan;
                } catch(e){
                    return false;
                }
            },

            hasDnsProblem() {
                return this.results && (!this.results.dns || !this.results.dns.dns || this.results.dns.dns.length === 0);
            },

            showTrackingButton(){
                return (this.results && this.addingStatus!==3)
                    && !this.hasDnsProblem
                    && ((this.results.canAddToList  && this.addingStatus!==1) || !this.hasAccount);
            },

            showLeWarning(){
                return !this.tlsScanError && this.tlsScanHostCert && this.tlsScan
                    && this.tlsScanHostCert && this.tlsScanHostCert.is_le
                    && this.tlsScanHostCert.valid_to_days<38.0 && this.tlsScanHostCert.valid_to_days > 0;
            },

            showWhoisWarning(){
                return this.results
                    && !this.hasDnsProblem
                    && this.results.whois
                    && this.results.whois.expires_at
                    && this.results.whois.expires_at_days
                    && this.results.whois.expires_at_days <= 3*30;
            },

            defconStyle(){
                return {
                    success: this.form.defcon===5,
                    warning: this.form.defcon<=4 && this.form.defcon>=2,
                    danger: this.form.defcon===1 };
            },

            curHostAddr(){
                return this.curJob ? (this.curJob.scan_host + (this.curJob.portString || '')) : '';
            },

            scanIp(){
                return !this.hasDnsProblem && this.tlsScan ? this.tlsScan.ip_scanned : null;
            },

            scanIpIsIpv6(){
                return this.scanIp && this.scanIp.indexOf(':') !== -1;
            },

            ips(){
                if (this.hasDnsProblem){
                    return [];
                }

                return _.map(this.results.dns.dns, x => {
                    return {
                        'type': x[0],
                        'ip': x[1],
                        'cur': this.scanIp === x[1]
                    };
                });
            },

            anotherIps(){
                return _.filter(this.ips, x => !x.cur);
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

            extendDateField(obj, key) {
                if (_.isEmpty(obj[key]) || _.isUndefined(obj[key])){
                    obj[key+'_utc'] = undefined;
                    obj[key+'_days'] = undefined;
                    return;
                }

                const utc = moment.utc(obj[key]).unix();
                obj[key+'_utc'] = utc;
                obj[key+'_days'] = Math.round(10 * (utc - moment().utc().unix()) / 3600.0 / 24.0) / 10;
            },

            pluralize(str, num, disp){
                return pluralize(str, num, disp);
            },

            recomp(){
                this.$emit('onRecompNeeded');
            },

            hookup(){
                const uuid = Req.findGetParameter('uuid');
                const url = _.trim(Req.findGetParameter('url'));
                const ip = _.trim(Req.findGetParameter('ip'));
                const new_job = Req.findGetParameter('new');
                const scanTarget = $('#scan-target');

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
                    this.curIp = ip;
                    setTimeout(this.submitForm, 550); // auto submit after page load
                }
            },

            didYouMeanUrlFull() {
                return this.newScanUrl(this.didYouMeanUrl);
            },

            newScanUrl(url, ip){
                let ret = '?url=' + encodeURIComponent(Req.removeWildcard(url && !_.isEmpty(url) ? url : this.curHostAddr));
                if (ip && !_.isEmpty(ip)){
                    ret += '&ip=' + encodeURIComponent(ip);
                }
                return ret;
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
                ga('send', 'event', 'spot-check', 'error');
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
                ga('send', 'event', 'spot-check', 'uuid-provided');
                this.pollTimer = setTimeout(this.pollFinish, 10);
            },

            pollFinish() {
                if (this.wsFinished){
                    return;
                }

                Req.getJobState(this.curUuid, json => {
                    console.log(json);

                    if (this.wsFinished){
                        return;
                    }

                    if (json.status !== 'success'){
                        this.errMsg('Job state fail, retry...');
                        this.pollTimer = setTimeout(this.pollFinish, this.wsStarted ? 7000 : 1000);
                        return;
                    }

                    this.curJob = json.job;
                    this.curJob.port = Req.defval(this.curJob.scan_port, 443);
                    if (this.curJob.state !== 'finished'){
                        this.pollTimer = setTimeout(this.pollFinish, this.wsStarted ? 7000 : 1000);
                    } else {
                        this.getResults();
                    }

                }, (jqxhr, textStatus, error) => {
                    this.errMsg('Could not load scan with given ID');
                });
            },

            onWsEvent(event){
                try {
                    // First websocket event received.
                    // Changes status polling timeouts.
                    this.wsStarted = true;
                    console.log('Scan State: ' + event.data.state);

                    // Finished event is terminating, load results directly.
                    if (event.data.state === 'finished'){
                        this.wsFinished = true;

                        // Cancel poll timer, load data directly
                        if (this.pollTimer){
                            clearTimeout(this.pollTimer);
                            this.pollTimer = null;
                        }

                        this.getResults();
                    }
                } catch(e){
                    console.warn(e);
                }
            },

            getResults() {
                Req.getJobResult(this.curUuid, json => {
                    if (json.status !== 'success'){
                        this.errMsg('Job results fail, retry...');
                        setTimeout(this.getResults, 1000);
                        return;
                    }

                    this.showResults(json);

                }, (jqxhr, textStatus, error) => {
                    this.errMsg('Could not get job results');
                });
            },

            showResults(json){
                this.results = json;
                this.curJob = json.job;
                this.curJob.port = Req.defval(this.curJob.scan_port, 443);

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
                this.unlistenWebsocket();
                this.processResults();
                this.processTlsScan();
                this.processCtScan();
                this.postprocessResults();
            },

            processResults() {
                // IP reset
                this.curIp = null;

                // Certificates
                const curTime = new Date().getTime() / 1000.0;
                for(const certId in this.results.certificates){
                    const cert = this.results.certificates[certId];
                    cert.valid_to_days = Math.round(10 * (cert.valid_to_utc - curTime) / 3600.0 / 24.0) / 10;
                    cert.valid_from_days = Math.round(10 * (curTime - cert.valid_from_utc) / 3600.0 / 24.0) / 10;
                    cert.matched_name = cert.matched_alt_names && cert.matched_alt_names.length > 0 ? cert.matched_alt_names[0] : cert.cname;
                }

                // DNS
                if (this.results.dns){
                    this.extendDateField(this.results.dns, 'last_scan_at');
                }

                // Whois
                if (this.results.whois){
                    this.extendDateField(this.results.whois, 'expires_at');
                    this.extendDateField(this.results.whois, 'last_scan_at');
                    this.extendDateField(this.results.whois, 'rec_updated_at');
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
                            'https', urlp.hostname, urlp.port || 443,
                            this.curJob.scan_scheme || 'https', this.curJob.scan_host, this.curJob.scan_port || 443)) {
                        this.didYouMeanUrl = 'https://' + urlp.host;
                    }
                }

                if (!this.didYouMeanUrl && this.tlsScan.follow_https_url){
                    const urlp = URL(this.tlsScan.follow_https_url, true);
                    if (!Req.isSameUrl(
                            'https', urlp.hostname, urlp.port || 443,
                            this.curJob.scan_scheme || 'https', this.curJob.scan_host, this.curJob.scan_port || 443)) {
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

                if (this.tlsScanHostCert){
                    this.tlsScanHostCert.issuerOrgNorm = this.certIssuer(this.tlsScanHostCert);
                }

                this.tlsScan.valid_trusted = this.tlsScan.valid_path && this.tlsScan.valid_hostname;
            },

            certIssuer(cert){
                if (!cert || _.isEmpty(cert) || !cert.issuer){
                    return null;
                }

                return Req.normalizeIssuer(Req.certIssuer(cert));
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
                this.curChannel = '';
                this.form.defcon = 5;
                this.form.textStatus = 'OK';
                this.jobSubmittedNow = false;
                this.resultsLoaded = false;
                this.wsStarted = false;
                this.wsFinished = false;
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
                this.pollTimer = null;
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

                ga('send', 'event', 'spot-check', 'check-submit');
                this.cleanResults();
                this.searchStarted({'host': targetUri});
                this.curUrl = targetUri;
                this.jobSubmittedNow = true;

                const req_data = {'scan-target': targetUri, 'ip':this.curIp};
                Req.submitJob(req_data, json => {
                    if (json.status !== 'success'){
                        this.errMsg('Could not submit the scan');
                        return;
                    }

                    this.$emit('onjobSubmitted', json);

                    // Install websocket listener
                    this.curChannel = 'spotcheck.' + json.uuid;
                    this.listenWebsocket();

                    // Update URL so it contains params - job ID & url
                    let new_url = window.location.pathname + "?uuid=" + json.uuid
                        + '&url=' + encodeURIComponent(targetUri);
                    if (this.curIp){
                        new_url += '&ip=' + encodeURIComponent(this.curIp);
                    }

                    try{
                        history.pushState(null, null, new_url); // new URL with history
                        history.replaceState(null, null, new_url); // replace the existing

                        this.curUuid = json.uuid;
                        this.pollTimer = setTimeout(this.pollFinish, 2500);

                    } catch(e) {
                        window.location.replace(new_url + '&new=1');
                    }

                }, (jqxhr, textStatus, error) => {
                    this.errMsg(error);
                });
            },

            listenWebsocket(){
                try {
                    window.Echo
                        .channel(this.curChannel)
                        .listen('.spotcheck.event', this.onWsEvent);

                } catch(e){
                    console.warn(e);
                }
            },

            unlistenWebsocket(){
                try{
                    Echo.leave(this.curChannel);
                } catch(e){
                    console.warn(e);
                }
            },

            startTracking(){
                const server2monitor = this.curUrl;
                ga('send', 'event', 'spot-check', 'start-tracking');

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

