<template>
    <div class="dashboard-wrapper">
        <div class="alert alert-danger scan-alert" id="search-error" style="display: none">
            <strong>Error!</strong> <span id="error-text"></span>
        </div>

        <div class="alert alert-info alert-waiting scan-alert" id="search-info"
             v-if="loadingState == 0">
            <span>Loading data, please wait...</span>
        </div>

        <div class="alert alert-info alert-waiting scan-alert"
             v-else-if="loadingState == 1">
            <span>Processing data ...</span>
        </div>

        <div class="alert alert-success scan-alert" id="search-success" style="display: none">
            <strong>Success!</strong> Scan finished.
        </div>

        <transition name="fade" v-on:after-leave="transition_hook">
        <div v-if="loadingState == 10">

            <!-- X Google Chart - renewal planner -->
            <!-- X Google Chart - renewal planner TLS+CT -->
            <!--   Google Chart - renewal planner historical -->
            <!--   Google Chart, pie - certificate ratio, LE / Cloudflare / Other -->
            <!--   Google Chart - Certificate coverage for domain? Downtime graph -->
            <!-- X DNS problem notices - resolution fails -->
            <!--   DNS changes over time -->
            <!-- X TLS connection fail notices - last attempt (connect fail, timeout, handshake) -->
            <!--   TLS certificate expired notices - last attempt -->
            <!--   TLS certificates trust problems (self signed, is_ca, empty chain, generic, HOSTNAME validation error) -->
            <!--   TLS certificate changes over time on the IP -->
            <!--   connection stats, small inline graphs? like status -->
            <!-- X Whois domain expiration notices -->
            <!--   CT only certificates to a table + chart -->
            <!--     how to detect CT only? was detected at some point? at some scan? new DB table for watch <-> cert assoc ? -->

            <div class="row">
                <div class="xcol-md-12">
                    <sbox>
                        <template slot="title">Monthly certificate renew planner</template>
                            <div class="form-group">
                                <!--<div id="columnchart_certificates" style="width: 100%; height: 350px;"></div>-->
                                <canvas id="columnchart_certificates_js" style="width: 100%; height: 350px;"></canvas>
                            </div>

                            <div class="form-group">
                                <!--<div id="columnchart_certificates_all" style="width: 100%; height: 350px;"></div>-->
                                <canvas id="columnchart_certificates_all_js" style="width: 100%; height: 350px;"></canvas>
                            </div>
                    </sbox>
                </div>
            </div>

            <!-- DNS lookup fails -->
            <div v-if="dnsFailedLookups.length > 0" class="row">
                <div class="xcol-md-12">
                    <sbox>
                        <template slot="title">Domain resolution problems</template>
                        <p>The following domains could not be resolved. Please check the validity.</p>
                        <div class="table-responsive table-xfull">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Domain</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="dns in dnsFailedLookups" class="danger">
                                <td>{{ dns.domain }}</td>
                            </tr>
                            </tbody>
                        </table>
                        </div>
                    </sbox>
                </div>
            </div>

            <!-- TLS connection fails -->
            <div v-if="tlsErrors.length > 0" class="row">
                <div class="xcol-md-12">
                    <sbox>
                        <template slot="title">Server connection problem</template>

                        <p>TLS connection could not be made to the following domains.</p>
                        <div class="table-responsive table-xfull">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>Domain</th>
                                    <th>Problem</th>
                                    <th>Detected</th>
                                    <th>Last scan</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="tls in tlsErrors" class="danger">
                                    <td>{{ tls.urlShort }}</td>
                                    <td>
                                        <span v-if="tls.err_code == 1">TLS handshake error</span>
                                        <span v-if="tls.err_code == 2">Connection error</span>
                                        <span v-if="tls.err_code == 3">Timeout</span>
                                    </td>
                                    <td>{{ new Date(tls.created_at_utc * 1000.0).toLocaleString() }}
                                         ({{ moment(tls.created_at_utc * 1000.0).fromNow() }})</td>
                                    <td>{{ new Date(tls.last_scan_at_utc * 1000.0).toLocaleString() }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </sbox>
                </div>
            </div>

            <!-- Imminent renewals -->
            <div v-if="showImminentRenewals" class="row">
                <div class="xcol-md-12">
                <sbox>
                    <template slot="title">Imminent Renewals (next 28 days)</template>
                    <div class="col-md-6">
                        <div class="table-responsive table-xfull">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>Deadline</th>
                                    <th>Relative</th>
                                    <th>Certificates</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="grp in imminentRenewalCerts">
                                    <td v-bind:class="grp[0].planCss.tbl">
                                        {{ new Date(grp[0].valid_to_utc * 1000.0).toLocaleDateString() }}</td>
                                    <td v-bind:class="grp[0].planCss.tbl">
                                        {{ moment(grp[0].valid_to_utc * 1000.0).fromNow() }} </td>
                                    <td v-bind:class="grp[0].planCss.tbl">
                                        {{ grp.length }} </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <canvas id="imminent_renewals_js" style="width: 100%; height: 300px;"></canvas>
                    </div>
                </sbox>
                </div>
            </div>

            <!-- Expiring domains -->
            <div v-if="showExpiringDomains" class="row">
                <div class="xcol-md-12">
                    <sbox>
                        <template slot="title">Expiring domains</template>
                        <p>Domains with expiration time in 1 year</p>
                        <div class="table-responsive table-xfull">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Deadline</th>
                                <th>Relative</th>
                                <th>Domain</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="cur_whois in sortBy(whois, 'expires_at_utc')" v-if="cur_whois.expires_at_days <= 365">
                                <td v-bind:class="cur_whois.planCss.tbl">
                                    {{ new Date(cur_whois.expires_at_utc * 1000.0).toLocaleDateString() }}</td>
                                <td v-bind:class="cur_whois.planCss.tbl">
                                    {{ moment(cur_whois.expires_at_utc * 1000.0).fromNow() }} </td>
                                <td v-bind:class="cur_whois.planCss.tbl">
                                    {{ cur_whois.domain }} </td>
                            </tr>

                            </tbody>
                        </table>
                        </div>
                    </sbox>
                </div>
            </div>

            <!-- Domains without expiration date detected - important, not to mislead it is fine -->
            <div v-if="showDomainsWithUnknownExpiration" class="row">
                <div class="xcol-md-12">
                    <sbox>
                        <template slot="title">Domains with unknown expiration</template>
                        <p>We were unable to detect expiration domain date for the following domains:</p>
                        <div class="table-responsive table-xfull">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Domain</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="cur_whois in whois" v-if="!cur_whois.expires_at_days" class="warning">
                                <td>{{ cur_whois.domain }}</td>
                            </tr>
                            </tbody>
                        </table>
                        </div>
                    </sbox>
                </div>
            </div>

            <!-- Certificate types -->
            <div class="row">
                <div class="xcol-md-12">
                    <sbox>
                        <template slot="title">Certificate types</template>
                        <div class="form-group">
                            <canvas id="pie_cert_types" style="width: 100%; height: 350px;"></canvas>
                        </div>
                    </sbox>
                </div>
            </div>

            <!-- Certificate issuers -->
            <div class="row" v-if="certIssuerTableData">
                <div class="xcol-md-12">
                    <sbox>
                        <template slot="title">Certificate issuers</template>
                        <div class="table-responsive table-xfull" style="margin-bottom: 10px">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Issuer</th>
                                <th>Active TLS</th>
                                <th>Certificate Transparency</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="curDat in certIssuerTableData">
                                <td> {{ curDat[0] }} </td>
                                <td> {{ curDat[1] }} </td>
                                <td> {{ curDat[2] }} </td>
                            </tr>
                            </tbody>
                        </table>
                        </div>

                        <div class="form-group">
                            <canvas id="pie_cert_issuers" style="width: 100%; height: 500px;"></canvas>
                        </div>
                    </sbox>
                </div>
            </div>

            <!-- Certificate list -->
            <div class="row">
                <div class="xcol-md-12">
                    <sbox>
                        <template slot="title">Certificate list</template>
                        <p>Active certificates found on servers ({{ len(tlsCerts) }})</p>
                        <div class="table-responsive table-xfull">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Expiration</th>
                                    <th>Relative</th>
                                    <th>Domains</th>
                                    <th>Issuer</th>
                                </tr>
                                </thead>

                                <tbody>
                                <tr v-for="cert in sortExpiry(tlsCerts)" v-if="cert.planCss">
                                    <td v-bind:class="cert.planCss.tbl">{{ cert.id }}</td>
                                    <td v-bind:class="cert.planCss.tbl">{{ cert.valid_to }}</td>
                                    <td v-bind:class="cert.planCss.tbl">{{ moment(cert.valid_to).fromNow() }}</td>
                                    <td v-bind:class="cert.planCss.tbl">
                                        <ul class="domain-list">
                                            <li v-for="domain in cert.watch_hosts">
                                                {{ domain }}
                                            </li>
                                        </ul>
                                    </td>
                                    <td v-bind:class="cert.planCss.tbl">{{ cert.issuerOrgNorm }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </sbox>
                </div>
            </div>

            <!-- All Certificate list -->
            <div class="row">
                <div class="xcol-md-12">
                    <sbox>
                        <template slot="title">Complete Certificate list</template>
                        <p>All certificates found ({{ len(certs) }})</p>
                        <div class="table-responsive table-xfull">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Expiration</th>
                                    <th>Relative</th>
                                    <th>Domains</th>
                                    <th>Issuer</th>
                                </tr>
                                </thead>

                                <tbody>
                                <tr v-for="cert in sortExpiry(certs)" v-if="cert.planCss">
                                    <td v-bind:class="cert.planCss.tbl">{{ cert.id }}</td>
                                    <td v-bind:class="cert.planCss.tbl">{{ cert.valid_to }}</td>
                                    <td v-bind:class="cert.planCss.tbl">{{ moment(cert.valid_to).fromNow() }}</td>
                                    <td v-bind:class="cert.planCss.tbl">
                                        <ul class="domain-list">
                                            <li v-for="domain in cert.watch_hosts_ct">
                                                {{ domain }}
                                            </li>
                                        </ul>
                                    </td>
                                    <td v-bind:class="cert.planCss.tbl">{{ cert.issuerOrgNorm }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </sbox>
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
    import VueCharts from 'vue-chartjs';
    import { Bar, Line } from 'vue-chartjs';
    import Chart from 'chart.js';

    export default {
        data: function() {
            return {
                loadingState: 0,
                results: null,
                dataProcessStart: null,
                useGoogleCharts: false,

                graphLibLoaded: false,
                graphsRendered: false,
                graphDataReady: false,

                crtTlsMonth: null,
                crtAllMonth: null,
                certTypesStats: null,
                certTypesStatsAll: null,
                certIssuerTableData: null,

                Req: window.Req,
                Laravel: window.Laravel,
                _: window._,

                chartColors: [
                    '#00c0ef',
                    '#f39c12',
                    '#00a65a',
                    '#f56954',
                    '#3c8dbc',
                    '#d2d6de',
                    '#ff6384',
                    '#ff9f40',
                    '#ffcd56',
                    '#4bc0c0',
                    '#36a2eb',
                    '#9966ff',
                    '#c9cbcf',
                    '#6655ef',
                    '#ffde56',
                    '#c43833',
                ]
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
                return {};
            },

            tlsCerts(){
                // return _.filter(this.certs, o => { return o.found_tls_scan; });
                return _.map(_.uniq(_.values(this.results.tls_cert_map)), x => {
                    return this.results.certificates[x];
                });
            },

            whois(){
                if (this.results && this.results.whois){
                    return this.results.whois;
                }
                return {};
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
            },

            dns(){
                if (this.results && this.results.dns){
                    return this.results.dns;
                }
                return {};
            },

            tls(){
                if (this.results && this.results.tls){
                    return this.results.tls;
                }
                return {};
            },

            dnsFailedLookups(){
                const r = _.filter(this.dns, x => {
                    return x && x.status !== 1;
                });
                return _.sortBy(r, [x => { return x.domain; }]);
            },

            tlsErrors(){
                return _.filter(this.tls, x => {
                    return x && x.status !== 1;
                });
            },

            week4renewals(){
                const r = _.filter(this.tlsCerts, x => {
                    return x && x.valid_to_days && x.valid_to_days >= 0 && x.valid_to_days <= 28;
                });
                const r2 = _.map(r, x => {
                    x.week4cat = this.week4grouper(x);
                    return x;
                });
                const grp = _.groupBy(r2, x => {
                    return x.week4cat;
                });
                return _.sortBy(grp, [x => {return x[0].valid_to_days; }]);
            },

            week4renewalsCounts(){
                const r = _.filter(this.tlsCerts, x => {
                    return x && x.valid_to_days && x.valid_to_days >= 0 && x.valid_to_days <= 28;
                });
                const ret = [0, 0, 0, 0];
                _.forEach(r, x => {
                    ret[this.week4grouper(x)] += 1;
                });
                return ret;
            },

            showWeek4renewals(){
                return _.sum(week4renewalsCounts) > 0;
            },

            tlsCertIssuers(){
                return this.certIssuersGen(this.tlsCerts);
            },

            allCertIssuers(){
                return this.certIssuersGen(this.certs);
            },
        },

        methods: {
            hookup(){
                setTimeout(this.loadData, 0);
                if (this.useGoogleCharts) {
                    google.charts.load('current', {'packages':['bar']});
                    google.charts.setOnLoadCallback(this.onGraphLibLoaded);
                }
            },

            //
            // Utility / helper functions / called from template directly
            //

            take(x, len){
                return _.take(x, len);
            },

            len(x) {
                if (x){
                    return _.size(x);
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

            moment(x){
                return moment(x);
            },

            transition_hook(el){
                this.recomp();
            },

            recomp(){
                this.$emit('onRecompNeeded');
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
            // Cert processing
            //

            week4grouper(x){
                if (x.valid_to_days <= 7){
                    return 0;
                } else if (x.valid_to_days <= 14){
                    return 1;
                } else if (x.valid_to_days <= 21){
                    return 2;
                } else {
                    return 3;
                }
            },

            certIssuer(cert){
                if (cert.is_le){
                    return 'Let\'s Encrypt';
                } else if (cert.is_cloudflare){
                    return 'Cloudflare';
                }

                const iss = cert.issuer;
                const ret = iss.match(/organizationName: (.+?)($|,\s[a-zA-Z0-9]+:)/);
                if (ret && ret[1]){
                    return ret[1];
                }

                return 'Other';
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

                // cache warmup while loading data
                this.warmup();
            },

            warmup(){
                setTimeout(() => {
                    Psl.get('test.now.sh');
                    Psl.get('test.通販');
                }, 10);
            },

            processData(){
                this.$nextTick(function () {
                    console.log('Data loaded');
                    this.dataProcessStart = moment();
                    this.processResults();
                });
            },

            processResults() {
                const curTime = new Date().getTime() / 1000.0;
                for(const watch_id in this.results.watches){
                    const watch = this.results.watches[watch_id];
                    const strPort = parseInt(watch.scan_port) || 443;
                    watch.url = Req.buildUrl(watch.scan_scheme, watch.scan_host, strPort);
                    watch.urlShort = Req.buildUrl(watch.scan_scheme, watch.scan_host, strPort === 443 ? undefined : strPort);
                }

                const fqdnResolver = _.memoize(Psl.get);
                const wildcardRemover = _.memoize(Req.removeWildcard);
                for(const certId in this.results.certificates){
                    const cert = this.results.certificates[certId];
                    cert.valid_to_dayfmt = moment(cert.valid_to_utc * 1000.0).format('YYYY-MM-DD');
                    cert.valid_to_monthfmt = moment(cert.valid_to_utc * 1000.0).format('YYYY-MM');
                    cert.valid_to_days = Math.round(10 * (cert.valid_to_utc - curTime) / 3600.0 / 24.0) / 10;
                    cert.valid_from_days = Math.round(10 * (curTime - cert.valid_from_utc) / 3600.0 / 24.0) / 10;
                    cert.watch_hosts = [];
                    cert.watch_urls = [];
                    cert.watch_hosts_ct = [];
                    cert.watch_urls_ct = [];
                    cert.alt_domains = _.sortedUniq(_.sortBy(_.map(_.castArray(cert.alt_names), x => {
                        return wildcardRemover(x);
                    })));
                    cert.alt_fqdns = _.sortedUniq(_.sortBy(_.map(_.castArray(cert.alt_domains), x => {
                        return fqdnResolver(x);  // too expensive now. 10 seconds for 150 certs. invoke later
                    })));

                    _.forEach(cert.tls_watches, watch_id => {
                        if (watch_id in this.results.watches){
                            cert.watch_hosts.push(this.results.watches[watch_id].scan_host);
                            cert.watch_urls.push(this.results.watches[watch_id].url);
                        }
                    });

                    _.forEach(_.uniq(_.union(cert.tls_watches, cert.crtsh_watches)), watch_id=>{
                        if (watch_id in this.results.watches){
                            cert.watch_hosts_ct.push(this.results.watches[watch_id].scan_host);
                            cert.watch_urls_ct.push(this.results.watches[watch_id].url);
                        }
                    });

                    cert.watch_hosts = _.uniq(cert.watch_hosts.sort());
                    cert.watch_urls = _.uniq(cert.watch_urls.sort());
                    cert.watch_hosts_ct = _.uniq(cert.watch_hosts_ct.sort());
                    cert.watch_urls_ct = _.uniq(cert.watch_urls_ct.sort());

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

                    cert.issuerOrg = this.certIssuer(cert);
                }

                this.normalizeValue(this.results.certificates, 'issuerOrg', 'issuerOrgNorm');

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

                for(const dns_id in this.results.dns){
                    const dns = this.results.dns[dns_id];
                    this.extendDateField(dns, 'last_scan_at');
                    dns.domain = this.results.watches && dns.watch_id in this.results.watches ?
                        this.results.watches[dns.watch_id].scan_host : undefined;
                }

                for(const tls_id in this.results.tls){
                    const tls = this.results.tls[tls_id];
                    this.extendDateField(tls, 'last_scan_at');
                    this.extendDateField(tls, 'created_at');
                    this.extendDateField(tls, 'updated_at');
                    if (this.results.watches && tls.watch_id in this.results.watches){
                        tls.domain = this.results.watches[tls.watch_id].scan_host;
                        tls.urlShort = this.results.watches[tls.watch_id].urlShort;
                    }
                }

                this.crtTlsMonth = this.monthDataGen(_.filter(this.tlsCerts, o => {
                    return o.valid_to_days >= 0 && o.valid_to_days < 365; }));
                this.crtAllMonth = this.monthDataGen(_.filter(this.certs, o => {
                    return o.valid_to_days >= 0 && o.valid_to_days < 365; }));
                this.certTypesStats = this.certTypes(this.tlsCerts);
                this.certTypesStatsAll = this.certTypes(this.certs);

                this.$set(this.results, 'certificates', this.results.certificates);
                this.$forceUpdate();
                this.$emit('onProcessed');
                this.loadingState = 10;

                this.$nextTick(function () {
                    this.graphDataReady = true;
                    this.graphLibLoaded = !this.useGoogleCharts;
                    this.renderCharts();
                    const processTime = moment().diff(this.dataProcessStart);
                    console.log('Processing finished in ' + processTime + ' ms');
                });
            },

            //
            // Graphs
            //

            onGraphLibLoaded(){
                this.graphLibLoaded = true;
                if (!this.graphsRendered){
                    this.$nextTick(function () {
                        setTimeout(this.renderCharts, 0);
                    });
                }
            },

            renderCharts(){
                if (!this.graphLibLoaded || !this.graphDataReady){
                    return;
                }

                this.graphsRendered = true;
                if (this.useGoogleCharts) {
                    this.renderGoogleGraphs();
                } else {
                    this.renderChartjs();
                }
            },

            renderChartjs(){
                this.plannerGraph();
                this.certTypesGraph();
                this.week4renewGraph();
                this.certIssuersGraph();
            },

            renderGoogleGraphs(){
                const rawCrtTlsData = _.concat([['Time', 'Let\'s Encrypt', 'Cloudflare', 'Other']], this.crtTlsMonth);
                const rawCrtAllData = _.concat([['Time', 'Let\'s Encrypt', 'Cloudflare', 'Other']], this.crtAllMonth);

                const crtTlsData = google.visualization.arrayToDataTable(rawCrtTlsData);
                const crtAllData = google.visualization.arrayToDataTable(rawCrtAllData);
                const baseOptions = {
                    backgroundColor: '#ecf0f5'
                };

                const crtTlsOptions = _.extend({
                    chart: {
                        title: 'Monthly planner - 12 months',
                        subtitle: 'TLS certificates for renewal',
                    }
                }, baseOptions);

                const crtAllOptions = _.extend({
                    chart: {
                        title: 'Monthly planner - 12 months (all)',
                        subtitle: 'TLS certificates for renewal, including CT certificates',
                    }
                }, baseOptions);


                const chartTls = new google.charts.Bar(document.getElementById('columnchart_certificates'));
                chartTls.draw(crtTlsData, google.charts.Bar.convertOptions(crtTlsOptions));
                const chartAll = new google.charts.Bar(document.getElementById('columnchart_certificates_all'));
                chartAll.draw(crtAllData, google.charts.Bar.convertOptions(crtAllOptions));
            },

            //
            // Subgraphs
            //

            plannerGraph(){
                let rawCrtTlsData = _.concat([['Time', 'Let\'s Encrypt', 'Cloudflare', 'Other']], this.crtTlsMonth);
                let rawCrtAllData = _.concat([['Time', 'Let\'s Encrypt', 'Cloudflare', 'Other']], this.crtAllMonth);
                rawCrtTlsData = this.graphDataConv(rawCrtTlsData);
                rawCrtAllData = this.graphDataConv(rawCrtAllData);

                const baseOptions = {
                    type: 'bar',
                    options: {
                        scaleBeginAtZero: true,
                        responsive: true,
                        maintainAspectRatio: true,
                        scaleShowGridLines: true,
                        scaleGridLineColor: "rgba(0,0,0,.02)",
                        scaleGridLineWidth: 1,
                        scales: {
                            xAxes: [{
                                stacked: true,
                            }],
                            yAxes: [{
                                stacked: true
                            }]
                        },
                        tooltips: {
                            mode: 'index',
                            intersect: false
                        },
                    }};

                const graphCrtTlsData = _.extend({data: rawCrtTlsData}, _.cloneDeep(baseOptions));
                graphCrtTlsData.options.title = {
                    display: true,
                    text: 'Monthly planner - 12 months'
                };

                const graphCrtAllData = _.extend({data: rawCrtAllData}, _.cloneDeep(baseOptions));
                graphCrtAllData.options.title = {
                    display: true,
                    text: 'Monthly planner - 12 months, all certs, CT'
                };

                new Chart(document.getElementById("columnchart_certificates_js"), graphCrtTlsData);
                new Chart(document.getElementById("columnchart_certificates_all_js"), graphCrtAllData);
            },

            certTypesGraph(){
                const graphCertTypes = {
                    type: 'doughnut',
                    data: {
                        datasets: [
                            {
                                data: this.certTypesStats,
                                backgroundColor: [this.chartColors[0], this.chartColors[1], this.chartColors[2]],
                                label: 'TLS active'
                            },
                            {
                                data: this.certTypesStatsAll,
                                backgroundColor: [this.chartColors[0], this.chartColors[1], this.chartColors[2]],
                                label: 'All TLS + CT'
                            }],
                        labels: [
                            'Let\'s Encrypt',
                            'Cloudflare',
                            'Other'
                        ]
                    },
                    options: {
                        responsive: true,
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Certificate types'
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                };

                new Chart(document.getElementById("pie_cert_types"), graphCertTypes);
            },

            week4renewGraph(){
                // graph config
                const config = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: this.week4renewalsCounts,
                            backgroundColor: [
                                this.chartColors[3],
                                this.chartColors[1],
                                this.chartColors[0],
                                this.chartColors[2],
                            ],
                            label: 'Renewals in 4 weeks'
                        }],
                        labels: [
                            "<= 7 days",
                            "7-14 days",
                            "15-21 days",
                            "22-28 days"
                        ]
                    },
                    options: {
                        responsive: true,
                        legend: {
                            position: 'right',
                        },
                        // title: {
                        //     display: true,
                        //     text: 'Renewals in 4 weeks'
                        // },
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                };

                setTimeout(() => {
                    new Chart(document.getElementById("imminent_renewals_js"), config);
                }, 1000);
            },

            certIssuersGraph(){
                const tlsIssuerStats = this.groupStats(this.tlsCertIssuers, 'count');
                const allIssuerStats = this.groupStats(this.allCertIssuers, 'count');
                this.mergeGroupStatsKeys([tlsIssuerStats, allIssuerStats]);
                this.mergedGroupStatSort([tlsIssuerStats, allIssuerStats], ['1', '0'], ['desc', 'asc']);
                this.certIssuerTableData = _.sortBy(
                    this.mergeGroupStatValues([tlsIssuerStats, allIssuerStats]),
                    x => {
                        return -1 * _.max(_.tail(x));
                    }
                );

                const tlsIssuerUnz = _.unzip(tlsIssuerStats);
                const allIssuerUnz = _.unzip(allIssuerStats);
                const graphCertTypes = {
                    type: 'doughnut',
                    data: {
                        datasets: [
                            {
                                data: tlsIssuerUnz[1],
                                backgroundColor: this.takeMod(this.chartColors, tlsIssuerUnz[0].length),
                                label: 'TLS active'
                            },
                            {
                                data: allIssuerUnz[1],
                                backgroundColor: this.takeMod(this.chartColors, allIssuerUnz[0].length),
                                label: 'All TLS + CT'
                            }],
                        labels: allIssuerUnz[0]
                    },
                    options: {
                        responsive: true,
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Certificate issuers'
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                };

                setTimeout(() => {
                    new Chart(document.getElementById("pie_cert_issuers"), graphCertTypes);
                }, 1000);
            },

            //
            // Common graph data gen
            //

            graphDataConv(data){
                // [[dataset names], [label, d1, d2, ...], [label, d1, d2, ...]]
                // converts to charjs data set format.
                if (_.isEmpty(data) || _.isEmpty(data[0])){
                    return {};
                }

                const ln = data[0].length;
                const labels = [];
                const datasets = [];
                for(let i=0; i<ln-1; i++){
                    datasets.push({
                        label: data[0][i+1],
                        backgroundColor: this.chartColors[i % this.chartColors.length],
                        data: []
                    });
                }

                _.forEach(data, function(value, idx){
                    if (idx===0){
                        return;
                    }
                    labels.push(value[0]);
                    for(let i=1; i < ln; i++){
                        datasets[i-1].data.push(value[i]);
                    }
                });
                return {labels: labels, datasets: datasets};
            },

            certTypes(certSet){
                // certificate type aggregation
                const certTypes = [0, 0, 0];  // LE, Cloudflare, Public / other

                for(const crtIdx in certSet){
                    const ccrt = certSet[crtIdx];
                    if (ccrt.is_le){
                        certTypes[0] += 1
                    } else if (ccrt.is_cloudflare){
                        certTypes[1] += 1
                    } else {
                        certTypes[2] += 1
                    }
                }
                return certTypes;
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
                    const certTypesStat = this.certTypes(grp);
                    const curEntry = [label, certTypesStat[0], certTypesStat[1], certTypesStat[2]];
                    ret.push(curEntry);
                    lastGrp = curMoment;
                }

                fillGap(ret, lastGrp, moment().add(1, 'year').add(1, 'month'));
                return ret;
            },

            certIssuersGen(certSet){
                const grp = _.groupBy(certSet, x => {
                    return x.issuerOrgNorm;
                });
                return grp; //return _.sortBy(grp, [x => {return x[0].issuerOrg; }]);
            },

            groupStats(grouped, sort){
                const agg = [];
                for(const curLabel in grouped){
                    agg.push([curLabel, grouped[curLabel].length]);  // zip
                }

                let sorted = agg;
                if (sort && (sort === 'label' || sort === 1)){
                    sorted = _.sortBy(sorted, x => { return x[0]; });
                } else if (sort && (sort === 'count' || sort === 2)){
                    sorted = _.sortBy(sorted, x => { return x[1]; });
                }

                return sorted;
            },

            mergeGroupStatsKeys(groups){
                // [g1 => [[l1,c1], [l2,c2]], ...]  - array of ziped datasets
                // after this function all datasets will have all keys, with defaul value 0 if it was not there before
                const keys = {};
                _.forEach(groups, x => {
                    _.assign(keys, this.listToSet(_.unzip(x)[0]));
                });

                _.forEach(groups, x => {
                    const curSet = this.listToSet(_.unzip(x)[0]);
                    _.forEach(keys, (val, key) => {
                        if (!(key in curSet)){
                            x.push([key, 0]);
                        }
                    });
                });
            },

            mergeGroupStatValues(groups){
                // [g1 => [[l1,c1], [l2,c2]], ...]  - array of ziped datasets
                // returns a new single dataset [[l1, c1, c2], ...]

                // key array
                const keys = _.reduce(groups, (acc, x)=>{
                    return _.union(acc, _.unzip(x)[0]);
                }, []);

                const ret = [];
                const grpObjs = _.map(groups, _.fromPairs);

                _.forEach(keys, key => {
                    const cur = [key];
                    _.forEach(grpObjs, grp => {
                        cur.push(key in grp ? grp[key] : 0);
                    });
                    ret.push(cur);
                });
                return ret;
            },

            mergeGroupStats(groups){
                // merges multiple datasets with zip-ed group structure, taking maximum count
                const x = _.reduce(groups, (result, value, key) => {
                    const cur = _.fromPairs(value);
                    return _.mergeWith(result, cur, (objValue, srcValue, key, object, source) => {
                        return _.max([objValue, srcValue]);
                    });
                }, {});

                return _.toPairs(x);
            },

            mergeGroups(groups){
                // [g1 => [gg1=>[], gg2=>[], ...], g2, ...]
                // modifies the given groups so they have same labels and fills 0 for missing pieces
                const allLabels = {};
                for(const grpid in groups){
                    const grp = groups[grpid];
                    for(const grpname in grp){
                        allLabels[grpname] = true;
                    }
                }

                for(const grpid in groups){
                    const grp = groups[grpid];
                    for(const curLabel in allLabels){
                        if (!(curLabel in grp)){
                            grp[curLabel] = [];
                        }
                    }
                }
            },

            mergedGroupStatSort(groups, fields, ordering){
                // [[[g1,c1], [g2, c2]], ...]
                // sorts each dataset separately based on the global ordering

                // finds global ordering on the group keys by the count
                const mixed = this.mergeGroupStats(groups);

                // global ordering on the mixed dataset, get ranking for the keys
                const ordered = _.orderBy(mixed, fields, ordering);

                // ranking on the keys: key -> ranking
                const ranking = _.zipObject(
                    _.unzip(ordered)[0],
                    _.range(ordered.length));

                // sort by global ranking, in-place sorting. for
                _.forEach(groups, (grp, idx) => {  // grp is the dataset
                    groups[idx].sort(Req.keyToCompare(y => {  // y is [g1, c1]
                        return ranking[y[0]];
                    }));

                    // returns new object - does not touch original ones
                    // groups[idx] = _.sortBy(grp, y => {  // y is [g1, c1]
                    //     return ranking[y[0]];
                    // });
                });
                return groups;
            },

            //
            // Universal utility methods
            //

            listToSet(lst){
                const st = {};
                for(const idx in lst){
                    st[lst[idx]] = true;
                }
                return st;
            },

            normalizeValue(col, field, newField){
                // normalizes a field in the collection col to the common most frequent value
                // collapsing function removes character defined by [^a-zA-Z0-9], then normalizes the groups.
                // adds a new field with the normalized value
                newField = newField || (_.isString(field) ? (field + '_new') : field);
                const vals = _.map(col, field);

                // group by normalized stripped form of the field
                const grps = _.groupBy(vals, x=>{
                    return _.lowerCase(_.replace(x, /[^a-zA-Z0-9]/g, ''));
                });

                // find the representative in the group - group by on subgroups, most frequent subgroup wins
                const subg = _.mapValues(grps, x => {
                    return _.groupBy(x);
                });

                // map back all variants of the field to the normalized key - used for normalization
                const normMap = {};
                _.forEach(subg, (val, key) => {
                    _.forEach(_.keys(val), x => {
                        normMap[x] = key;
                    })
                });

                // mapped -> representant
                const repr = _.mapValues(subg, x => {
                    if (_.size(x) === 1){
                        return _.keys(x)[0];
                    }
                    return _.reduce(x, (acc, val, key) => {
                        return _.size(x[key]) > _.size(x[acc]) ? key : acc;
                    });
                });

                // a bit of polishing of the representants
                const frep = _.mapValues(repr, this.capitalizeFirstWord);
                
                // normalization step -> add a new field
                return _.map(col, x => {
                    const curField = _.isFunction(field) ? field(x) : x[field];
                    x[newField] = frep[normMap[curField]];
                    return x;
                });
            },

            capitalizeFirstWord(str){
                const r = _.replace(str, /^[A-Z]+\b/, _.capitalize);
                return _.replace(r, /^[a-z]+\b/, _.capitalize);
            },

            takeMod(set, len){
                const ret = [];
                const ln = set.length;
                for(let i = 0; i<len; i++){
                    ret.push(set[i % ln]);
                }
                return ret;
            },

            //
            // Misc
            //

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

    .fade-enter-active, .fade-leave-active {
        transition: opacity 1.0s
    }
    .fade-enter, .fade-leave-to /* .fade-leave-active in <2.1.8 */ {
        opacity: 0
    }

    .box-body > .table-xfull {
        margin-left: -10px;
        margin-right: -10px;
        margin-bottom: -10px;
        width: auto;
    }

    .box-body > .table-xfull > .table {
        margin-bottom: auto;
    }

    .box-body > .table-xfull > .table > thead > tr > th,
    .box-body > .table-xfull > .table > tbody > tr > td
    {
        padding-left: 12px;
    }

</style>

