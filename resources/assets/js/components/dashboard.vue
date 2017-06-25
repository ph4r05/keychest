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
        <div v-if="loadingState == 10">

            <!-- Google Chart - renewal planner -->
            <!-- Google Chart - renewal planner TLS+CT -->
            <!-- Google Chart - renewal planner historical -->
            <!-- Google Chart, pie - certificate ratio, LE / Cloudflare / Other -->
            <!-- DNS problem notices -->
            <!-- DNS changes over time -->
            <!-- TLS connection fail notices - last attempt -->
            <!-- TLS certificate expired notices - last attempt -->
            <!-- TLS certificate changes over time on the IP -->
            <!-- connection stats, small inline graphs? like status -->
            <!-- Whois domain expiration notices -->

            <!-- Imminent renewals -->
            <div v-if="showImminentRenewals" class="row">
                <div class="col-md-12">
                <h3>Imminent Renewals (next 28 days)</h3>
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Deadline</th>
                            <th>Certificates</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="grp in imminentRenewalCerts">
                            <td v-bind:class="grp[0].planCss.tbl">{{ new Date(grp[0].valid_to_utc * 1000.0).toLocaleDateString() }}</td>
                            <td v-bind:class="grp[0].planCss.tbl">{{ grp.length }} </td>
                        </tr>

                    </tbody>
                </table>
                </div>
            </div>

            <!-- Expiring domains -->
            <div v-if="showExpiringDomains" class="row">
                <div class="col-md-12">
                    <h3>Expiring domains</h3>
                    <p>Domains with expiration time in 1 year</p>
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                        <tr>
                            <th>Deadline</th>
                            <th>Domain</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="cur_whois in sortBy(whois, 'expires_at_utc')" v-if="cur_whois.expires_at_days <= 365">
                            <td v-bind:class="cur_whois.planCss.tbl"> {{ new Date(cur_whois.expires_at_utc * 1000.0).toLocaleDateString() }} </td>
                            <td v-bind:class="cur_whois.planCss.tbl"> {{ cur_whois.domain }} </td>
                        </tr>

                        </tbody>
                    </table>
                    <!-- TODO: domains with unknown time - show it here -->

                </div>
            </div>
            <div v-if="showDomainsWithUnknownExpiration" class="row">
                <div class="col-md-12">
                    <h3>Domains with unknown expiration</h3>
                    <p>We were unable to detect expiration domain date for the following domains:</p>
                    <ul class="coma-list">
                        <li v-for="cur_whois in whois" v-if="!cur_whois.expires_at_days">{{ cur_whois.domain }}</li>
                    </ul>
                </div>
            </div>

            <!-- Certificate list -->
            <div class="row">
                <div class="col-md-12">
                    <h3>Certificate list</h3>
                    <p>Active certificates found on servers</p>
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Expiration</th>
                            <th>Type</th>
                            <th>Domains</th>
                        </tr>
                        </thead>

                        <tbody>
                        <tr v-for="cert in sortExpiry(tlsCerts)" v-if="cert.planCss">
                            <td v-bind:class="cert.planCss.tbl">{{ cert.id }}</td>
                            <td v-bind:class="cert.planCss.tbl">{{ cert.valid_to }}</td>
                            <td v-bind:class="cert.planCss.tbl">{{ cert.type }}</td>
                            <td v-bind:class="cert.planCss.tbl">
                                <ul class="domain-list">
                                    <li v-for="domain in cert.watch_hosts">
                                        {{ domain }}
                                    </li>
                                </ul>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>


        </div>
        </transition>
    </div>

</template>

<script>
    import axios from 'axios';
    import moment from 'moment';
    import sprintf from 'sprintf-js';

    export default {
        data: function() {
            return {
                loadingState: 0,
                results: null,

                crtTlsMonth: null,
                crtAllMonth: null,

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

            certs(){
                if (this.results && this.results.certificates){
                    return this.results.certificates;
                }
                return [];
            },

            tlsCerts(){
                return _.filter(this.certs, o => { return o.found_tls_scan; });
            },

            whois(){
                if (this.results && this.results.whois){
                    return this.results.whois;
                }
                return [];
            },

            showImminentRenewals(){
                return _.reduce(this.tlsCerts, (acc, cur) => {
                    return acc + (cur.valid_to_days <= 28);
                }, 0) > 0;
            },

            showExpiringDomains(){
                return _.reduce(this.whois, (acc, cur) => {
                        return acc + (cur.expires_at_days <= 365);
                    }, 0) > 0;
            },

            showDomainsWithUnknownExpiration(){
                return _.reduce(this.whois, (acc, cur) => {
                        return acc + (!cur.expires_at_days);
                    }, 0) > 0;
            },

            imminentRenewalCerts(){
                const imm = _.filter(this.tlsCerts, x => { return x.valid_to_days <= 28 });
                const grp = _.groupBy(imm, x => {
                    return x.valid_to_dayfmt;
                });
                return _.sortBy(grp, [x => {return x[0].valid_to_days; }]);
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

            extendDateField(obj, key) {
                if (_.isEmpty(obj[key]) || _.isUndefined(obj[key])){
                    obj[key+'_utc'] = undefined;
                    obj[key+'_days'] = undefined;
                    return;
                }

                const utc = moment(obj[key]).unix();
                obj[key+'_utc'] = utc;
                obj[key+'_days'] = Math.round(10 * (utc - moment().unix()) / 3600.0 / 24.0) / 10;
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

            sortBy(x, fld){
                return _.sortBy(x, [ (o) => { return o[fld]; } ] );
            },

            sortExpiry(x){
                return _.sortBy(x, [ (o) => { return o.valid_to_utc; } ] );
            },

            //
            // Data processing
            //

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
                this.$nextTick(function () {
                    console.log('process data now...');
                    this.processResults();
                });
            },

            processResults() {
                const curTime = new Date().getTime() / 1000.0;
                for(const watch_id in this.results.watches){
                    const watch = this.results.watches[watch_id];
                    watch.url = Req.buildUrl(watch.scan_scheme, watch.scan_host, watch.scan_port);
                }

                for(const certId in this.results.certificates){
                    const cert = this.results.certificates[certId];
                    cert.valid_to_dayfmt = moment(cert.valid_to_utc * 1000.0).format('YYYY-MM-DD');
                    cert.valid_to_monthfmt = moment(cert.valid_to_utc * 1000.0).format('YYYY-MM');
                    cert.valid_to_days = Math.round(10 * (cert.valid_to_utc - curTime) / 3600.0 / 24.0) / 10;
                    cert.valid_from_days = Math.round(10 * (curTime - cert.valid_from_utc) / 3600.0 / 24.0) / 10;
                    cert.watch_hosts = [];
                    cert.watch_urls = [];
                    for(const ii in cert.tls_watches){
                        const watch_id = cert.tls_watches[ii];
                        if (watch_id in this.results.watches){
                            cert.watch_hosts.push(this.results.watches[watch_id].scan_host);
                            cert.watch_urls.push(this.results.watches[watch_id].url);
                        }
                    }

                    cert.watch_hosts = _.uniq(cert.watch_hosts.sort());
                    cert.watch_urls = _.uniq(cert.watch_urls.sort());
                    cert.planCss = {tbl: {
                        'success': cert.valid_to_days > 14 && cert.valid_to_days <= 28,
                        'warning': cert.valid_to_days > 7 && cert.valid_to_days <= 14,
                        'warning-hi': cert.valid_to_days <= 7,
                    }};

                    if (cert.is_le) {
                        cert.type = 'Let\'s Encrypt';
                    } else if (cert.is_cloudflare){
                        cert.type = 'Cloudflare';
                    } else {
                        cert.type = 'Public';
                    }
                }

                for(const whois_id in this.results.whois){
                    const whois = this.results.whois[whois_id];
                    this.extendDateField(whois, 'expires_at');
                    this.extendDateField(whois, 'registered_at');
                    this.extendDateField(whois, 'rec_updated_at');
                    this.extendDateField(whois, 'last_scan_at');
                    whois.planCss = {tbl: {
                        'success': whois.expires_at_days > 3*28 && whois.expires_at_days <= 6*28,
                        'warning': whois.expires_at_days > 28 && whois.expires_at_days <= 3*28,
                        'warning-hi': whois.expires_at_days > 14 && whois.expires_at_days <= 28,
                        'danger': whois.expires_at_days <= 14,
                    }};
                }

                this.crtTlsMonth = this.monthDataGen(_.filter(this.tlsCerts, o => {
                    return o.valid_to_days >= 0 && o.valid_to_days < 365; }));
                this.crtAllMonth = this.monthDataGen(_.filter(this.certs, o => {
                    return o.valid_to_days >= 0 && o.valid_to_days < 365; }));

                this.$set(this.results, 'certificates', this.results.certificates);
                this.$forceUpdate();
                this.$emit('onProcessed');
                this.loadingState = 10;
            },


            monthDataGen(certSet){
                // cert per months, LE, Cloudflare, Others
                const grp = _.groupBy(certSet, x => {
                    return x.valid_to_monthfmt;
                });

                const fillGap = (ret, lastMoment, toMoment) => {
                    if (_.isUndefined(lastGrp) || lastMoment >= toMoment){
                        return;
                    }

                    const terminal = toMoment.format('MM/YY');
                    const i = moment(lastMoment).add(1, 'month');
                    for(i; i.format('MM/YY') !== terminal && i < toMoment; i.add(1, 'month')){
                        ret.push([ i.format('MM/YY'), 0, 0, 0]);
                    }
                };

                const sorted = _.sortBy(grp, [x => {return x[0].valid_to_utc; }]);
                const ret = [];
                let lastGrp = undefined;
                for(const idx in sorted){
                    const grp = sorted[idx];
                    const crt = grp[0];
                    const curMoment = moment(crt.valid_to_utc * 1000.0);
                    const label = curMoment.format('MM/YY');

                    fillGap(ret, lastGrp, curMoment);
                    const curEntry = [label, 0, 0, 0];

                    for(const crtIdx in grp){
                        const ccrt = grp[crtIdx];
                        if (ccrt.is_le){
                            curEntry[1] += 1
                        } else if (ccrt.is_cloudflare){
                            curEntry[2] += 1
                        } else {
                            curEntry[3] += 1
                        }
                    }

                    ret.push(curEntry);
                    lastGrp = curMoment;
                }

                fillGap(ret, lastGrp, moment().add(1, 'year').add(1, 'month'));
                return ret;
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
    ul.domain-list {
        padding-left: 0;
    }

    ul.domain-list li {
        list-style-type: none;
    }

    .coma-list {
        display: inline;
        list-style: none;
        padding-left: 0;
    }

    .coma-list li {
        display: inline;
    }

    .coma-list li:after {
        content: ", ";
    }

    .coma-list li:last-child:after {
        content: "";
    }

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

