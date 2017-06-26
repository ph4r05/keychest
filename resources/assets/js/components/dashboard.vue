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
            <!-- Google Chart - Certificate coverage for domain? Downtime graph -->
            <!-- DNS problem notices - resolution fails -->
            <!-- DNS changes over time -->
            <!-- TLS connection fail notices - last attempt (connect fail, timeout, handshake) -->
            <!-- TLS certificate expired notices - last attempt -->
            <!-- TLS certificates trust problems (self signed, is_ca, empty chain, generic, HOSTNAME validation error) -->
            <!-- TLS certificate changes over time on the IP -->
            <!-- connection stats, small inline graphs? like status -->
            <!-- Whois domain expiration notices -->

            <div class="row">
                <div class="col-md-12">
                    <h3>Monthly certificate renew planner</h3>
                    <div class="form-group">
                        <!--<div id="columnchart_certificates" style="width: 100%; height: 350px;"></div>-->
                        <canvas id="columnchart_certificates_js" style="width: 100%; height: 350px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <!--<div id="columnchart_certificates_all" style="width: 100%; height: 350px;"></div>-->
                        <canvas id="columnchart_certificates_all_js" style="width: 100%; height: 350px;"></canvas>
                    </div>
                </div>
            </div>

            <div v-if="dnsFailedLookups.length > 0" class="row">
                <div class="col-md-12">
                    <h3>Domain resolution problems</h3>
                    <p>The </p>
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
            </div>

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
                    <div class="form-group">
                        <ul class="coma-list">
                            <li v-for="cur_whois in whois" v-if="!cur_whois.expires_at_days">{{ cur_whois.domain }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Certificate types -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <h3>Certificate types</h3>
                        <canvas id="pie_cert_types" style="width: 100%; height: 350px;"></canvas>
                    </div>
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
    import VueCharts from 'vue-chartjs';
    import { Bar, Line } from 'vue-chartjs';
    import Chart from 'chart.js';

    export default {
        data: function() {
            return {
                loadingState: 0,
                results: null,
                useGoogleCharts: false,

                graphLibLoaded: false,
                graphsRendered: false,
                graphDataReady: false,

                crtTlsMonth: null,
                crtAllMonth: null,
                certTypesStats: null,
                certTypesStatsAll: null,

                Req: window.Req,
                Laravel: window.Laravel,

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
                    '#c9cbcf'
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
            },

            dns(){
                if (this.results && this.results.dns){
                    return this.results.dns;
                }
                return [];
            },

            dnsFailedLookups(){
                const r = _.filter(this.dns, x => {
                    return x && x.status !== 1;
                });
                return _.sortBy(r, [x => { return x.domain; }]);
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
                if (this.useGoogleCharts) {
                    google.charts.load('current', {'packages':['bar']});
                    google.charts.setOnLoadCallback(this.onGraphLibLoaded);
                }
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

                for(const dns_id in this.results.dns){
                    const dns = this.results.dns[dns_id];
                    dns.domain = this.results.watches && dns.watch_id in this.results.watches ?
                        this.results.watches[dns.watch_id].scan_host : undefined;
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
                });
            },

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

            renderChartjs(){
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

                // Cert types
                const graphCertTypes = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
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

                new Chart(document.getElementById("columnchart_certificates_js"), graphCrtTlsData);
                new Chart(document.getElementById("columnchart_certificates_all_js"), graphCrtAllData);
                new Chart(document.getElementById("pie_cert_types"), graphCertTypes);
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
</style>

