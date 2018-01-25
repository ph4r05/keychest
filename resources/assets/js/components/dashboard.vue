<template>
    <div class="dashboard-wrapper">
        <div class="row">
            <div class="alert alert-danger scan-alert" id="search-error" style="display: none">
                <strong>Error!</strong> <span id="error-text"></span>
            </div>

            <div class="alert alert-info alert-waiting scan-alert" id="search-info"
                 v-if="loadingState === 0">
                <span>Loading data, please wait...</span>
            </div>

            <div class="alert alert-info alert-waiting scan-alert"
                 v-else-if="loadingState === 1">
                <span>Processing data ...</span>
            </div>

            <div class="alert alert-success scan-alert" id="search-success" style="display: none">
                <strong>Success!</strong> Scan finished.
            </div>
        </div>

        <transition name="fade" v-on:after-leave="transition_hook">
        <div v-if="loadingState === 10">

            <!-- Header info widgets -->
            <div class="row">

                <!-- HEADLINE: certificates expire now -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box"
                         v-bind:class="{'bg-green': numExpiresNow < 1, 'bg-red': numExpiresNow > 0}" >
                        <div class="inner">
                            <h3>{{ numExpiresNow }}</h3>

                            <p v-if="numExpiresNow < 1">Certificates expire now</p>
                            <p v-else-if="numExpiresNow < 2">Certificate expires now</p>
                            <p v-else="">Certificates expire now</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-exclamation-circle"></i>
                        </div>
                        <a href="#renewals" class="small-box-footer"
                           v-if="numExpiresNow > 0">Find details <i class="fa fa-arrow-circle-right"></i></a>
                        <a href="https://www.tripadvisor.co.uk/Search?geo=&latitude=&longitude=&searchNearby=&redirect=&startTime=&uiOrigin=&q=short+breaks"
                           v-else-if="numExpiresSoon>0"
                           target="_blank"
                           class="small-box-footer">Take a short break <i class="fa fa-arrow-circle-right"></i></a>
                        <a href="#" class="small-box-footer" v-else="">This looks good</a>
                    </div>
                </div>
                <!-- ./col -->

                <!-- HEADLINE: certificates expire soon -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box"
                        v-bind:class="{'bg-green': (numExpiresSoon - numExpiresNow) < 1, 'bg-yellow': (numExpiresSoon - numExpiresNow) > 0}" >
                        <div class="inner">
                            <h3>{{ numExpiresSoon - numExpiresNow }}</h3>
                            <p v-if="(numExpiresSoon - numExpiresNow) < 1">Certificates expire soon</p>
                            <p v-else-if="(numExpiresSoon - numExpiresNow) < 2">Certificate expires soon</p>
                            <p v-else="">Certificates expire soon</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-bell"></i>
                        </div>
                        <a href="#renewals"
                           class="small-box-footer" v-if="numExpiresSoon > 0">More info <i class="fa fa-arrow-circle-right"></i></a>
                        <a v-else-if="numExpiresSoon === 0"
                           target="_blank"
                           href="https://www.tripadvisor.co.uk/Search?geo=&latitude=&longitude=&searchNearby=&redirect=&startTime=&uiOrigin=&q=holiday"
                           class="small-box-footer">Take a holiday <i class="fa fa-arrow-circle-right"></i></a>
                        <a v-else=""
                           href="#"
                           class="small-box-footer">A break after this week</a>
                    </div>
                </div>
                <!-- ./col -->

                <!-- HEADLINE: certificate inventory -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-aqua">
                        <div class="inner">
                            <h3>{{ len(tlsCerts) }} / {{ numHiddenCerts+len(tlsCerts) }}</h3>

                            <p>Active / All certificates</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-heartbeat"></i>
                        </div>
                        <a href="#certs" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->

                <!-- HEADLINE: no of servers -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-aqua" v-if="dnsFailedLookups.length+ tlsErrors.length < 1 ">
                        <div class="inner">
                            <h3>{{ numWatches }}</h3>
                            <p>Watched servers</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-server"></i> <!--fa-sitemap-->
                        </div>
                        <!--suppress HtmlUnknownTarget -->
                        <a href="/home/servers" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>

                    <div class="small-box bg-yellow" v-else="">
                        <div class="inner">
                            <h3>{{ dnsFailedLookups.length+ tlsErrors.length }} / {{ numWatches }}</h3>
                            <p>Watched servers DOWN</p>
                        </div>

                        <div class="icon">
                            <i class="fa fa-server"></i> <!--fa-sitemap-->
                        </div>
                        <!--suppress HtmlUnknownTarget -->
                        <a href="/home/servers" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->

            </div>

            <!-- Section heading -->
            <div class="row">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-tachometer"></i></span>
                    <div class="info-box-content info-box-label">
                        Key Management Report - {{ curDateUsString() }}
                        <!-- TODO: fix: Date(tls.created_at_utc * 1000.0 - (new Date().getTimezoneOffset())*60) -->
                    </div>
                </div>
                <p class="tc-onyx">This dashboard contains latest available information for your servers and certificates. If you've
                made recent changes to some of your servers and these are not yet reflected in the dashboard, please use
                    the Spot Check function to get the real-time status.<br><br></p>

            </div>

            <!-- Monthly planner -->
            <cert-planner
                    :certs="certs"
                    :tls-certs="tlsCerts"
                    :cdn-certs="cdnCerts"
            />

            <!-- incident summary table -->
            <a name="incidentSummary"></a>
            <incident-summary
                    :len-dns-failed-lookups="len(dnsFailedLookups)"
                    :len-tls-errors="len(tlsErrors)"
                    :len-expired-certificates="len(expiredCertificates)"
                    :len-tls-invalid-trust="len(tlsInvalidTrust)"
                    :len-tls-invalid-hostname="len(tlsInvalidHostname)"
            />

            <!-- Section heading -->
            <incidents
                    :dns-failed-lookups="dnsFailedLookups"
                    :tls-errors="tlsErrors"
                    :expired-certificates="expiredCertificates"
                    :tls-invalid-trust="tlsInvalidTrust"
                    :tls-invalid-hostname="tlsInvalidHostname"
            />

            <!-- Section heading - PLANNING -->
            <div class="row">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-calendar-check-o"></i></span>
                    <div class="info-box-content info-box-label">
                        Planning
                    </div>
                </div>
            </div>

            <!-- Imminent renewals -->
            <a name="renewals"></a>
            <imminent-renewals
                    :certs="certs"
                    :tls-certs="tlsCerts"
            />

            <!-- Expiring domains -->
            <div v-if="showExpiringDomains" class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-success" :headerCollapse="true">
                        <template slot="title">Domain name expiration dates</template>
                        <p>The following domain names' registration expires within 90 days.</p>

                        <expiring-domains-table :whois="whois"/>
                    </sbox>
                </div>
            </div>

            <!-- Domains without expiration date detected - important, not to mislead it is fine -->
            <div v-if="showDomainsWithUnknownExpiration" class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-warning" :headerCollapse="true">
                        <template slot="title">Domains with unknown expiration</template>
                        <p>We were unable to detect expiration domain date for the following domains:</p>

                        <unknown-expiration-domains-table :whois="whois"/>
                    </sbox>
                </div>
            </div>

            <!-- Section heading - INFORMATIONAL -->
            <div class="row">
                <div class="info-box">
                    <span class="info-box-icon bg-blue"><i class="fa fa-info-circle"></i></span>
                    <div class="info-box-content info-box-label">
                        Informational
                    </div>
                </div>
            </div>

            <!-- Certificate types -->
            <cert-types
                    :certs="certs"
                    :tls-certs="tlsCerts"
                    :cdn-certs="cdnCerts"
            />

            <!-- Certificate issuers -->
            <cert-issuers
                    :certs="certs"
                    :tls-certs="tlsCerts"
            />

            <!-- Certificate domains -->
            <cert-domains
                    :certs="certs"
                    :tls-certs="tlsCerts"
            />

            <!-- Certificate list -->
            <a name="certs"></a>
            <tls-certs
                    :tlsCerts="tlsCerts"
                    @include-not-verified="val => { includeNotVerified = val }"
            />


            <!-- All Certificate list -->
            <a name="allCerts"></a>
            <all-certs
                    :certs="certs"
                    @include-expired="val => { includeExpired = val }"
            />

        </div>
        </transition>
    </div>

</template>

<script>
    import _ from 'lodash';
    import axios from 'axios';
    import moment from 'moment';

    import Psl from 'ph4-psl';
    import Req from 'req';
    import ReqD from 'req-data';
    import util from './dashboard/code/util';
    import charts from './dashboard/code/charts';

    import VueCharts from 'vue-chartjs';
    import VeeValidate from 'vee-validate';
    import ToggleButton from 'vue-js-toggle-button';
    import Chart from 'chart.js';
    import toastr from 'toastr';
    import Vue from 'vue';

    import DashboardCertPlanner from './dashboard/CertPlanner';
    import DashboardCertIssuers from './dashboard/CertIssuers';
    import DashboardCertDomains from './dashboard/CertDomains';
    import DashboardCertRenewals from './dashboard/CertRenewals';
    import DashboardCertTypes from './dashboard/CertTypes';
    import DashboardIncidentSummary from './dashboard/IncidentSummary';
    import DashboardIncidents from './dashboard/Incidents';
    import DashboardCertTlsList from './dashboard/CertTlsList';
    import DashboardCertAllList from './dashboard/CertAllList';

    import DashboardExpiringDomainsTable from './dashboard/tables/ExpiringDomainsTable';
    import DashboardUnknownExpirationDomainsTable from './dashboard/tables/UnknownExpirationDomainsTable';

    import './dashboard/css/dashboard.css';
    import IncidentSummary from "./dashboard/IncidentSummary";

    Vue.use(ToggleButton);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    export default {
        components: {
            'cert-planner': DashboardCertPlanner,
            'cert-issuers': DashboardCertIssuers,
            'cert-domains': DashboardCertDomains,
            'cert-types': DashboardCertTypes,
            'imminent-renewals': DashboardCertRenewals,
            'incident-summary': DashboardIncidentSummary,
            'incidents': DashboardIncidents,
            'tls-certs': DashboardCertTlsList,
            'all-certs': DashboardCertAllList,

            'expiring-domains-table': DashboardExpiringDomainsTable,
            'unknown-expiration-domains-table': DashboardUnknownExpirationDomainsTable,
        },

        data: function() {
            return {
                loadingState: 0,
                results: null,
                dataProcessStart: null,

                graphsRendered: false,
                graphDataReady: false,

                includeExpired: false,
                includeNotVerified: false,

                Laravel: window.Laravel,
            };
        },

        mounted() {
            this.$nextTick(() => {
                this.hookup();
            })
        },

        computed: {
            hasAccount(){
                return !this.Laravel.authGuest;
            },

            tlsCertsIdsMap(){
                if (!this.results || !this.results.watch_to_tls_certs){
                    return {};
                }

                return Req.listToSet(_.uniq(_.flattenDeep(_.values(this.results.watch_to_tls_certs))));
            },

            cdnCerts() {
                if (!this.results || !this.results.tls || !this.results.certificates){
                    return {};
                }

                return util.cdnCerts(this.results.tls, this.results.certificates);
            },

            tlsCerts(){
                return _.map(_.keys(this.tlsCertsIdsMap), x => {
                    return this.results.certificates[x];
                });
            },

            allCerts(){
                if (!this.results || !this.results.certificates){
                    return {};
                }

                return this.results.certificates;
            },

            certs(){
                if (!this.results || !this.results.certificates){
                    return {};
                }

                return _.filter(this.results.certificates, x=>{
                    return this.includeExpired || (x.id in this.tlsCertsIdsMap) || (x.valid_to_days >= -28);
                });
            },

            whois(){
                if (this.results && this.results.whois){
                    return this.results.whois;
                }
                return {};
            },

            numHiddenCerts(){
                return Number(_.size(this.certs) - _.size(this.tlsCerts));
            },

            numExpiresSoon(){
                return Number(_.sumBy(this.tlsCerts, cur => {
                    return (cur.valid_to_days <= 28 && cur.valid_to_days >= -28);
                }));
            },

            numExpiresNow(){
                return Number(_.sumBy(this.tlsCerts, cur => {
                    return (cur.valid_to_days <= 8 && cur.valid_to_days >= -28);
                }));
            },

            numWatches(){
                return this.results ? _.size(this.results.watches) : 0;
            },

            showExpiringDomains(){
                return _.reduce(this.whois, (acc, cur) => {
                        return acc + (cur.expires_at_days <= 90);
                    }, 0) > 0;
            },

            showDomainsWithUnknownExpiration(){
                return _.reduce(this.whois, (acc, cur) => {
                        return acc + (!cur.expires_at_days);
                    }, 0) > 0;
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
                return util.tlsErrors(this.tls);
            },

            expiredCertificates(){
                return _.filter(this.tlsCerts, x => {
                    return x.is_expired;
                });
            },

            tlsInvalidTrust(){
                return _.filter(this.tls, x => {
                    return x && x.status === 1 && !x.valid_path;
                });
            },

            tlsInvalidHostname(){
                return _.filter(this.tls, x => {
                    return x && x.status === 1 && x.valid_path && !x.valid_hostname;
                });
            },
        },

        methods: {
            hookup(){
                setTimeout(this.loadData, 0);
            },

            //
            // Utility / helper functions / called from template directly
            //

            take: util.take,
            len: util.len,
            utcTimeLocaleString: util.utcTimeLocaleString,
            utcTimeLocaleStringUs: Req.del(util.utcTimeLocaleStringUs, util),
            utcTimeLocaleDateString: util.utcTimeLocaleDateString,
            utcTimeLocaleDateStringUs: Req.del(util.utcTimeLocaleDateStringUs, util),
            curDateUsString: Req.del(util.curDateUsString, util),
            extendDateField: util.extendDateField,
            moment: util.moment,
            momentu: util.momentu,
            sortBy: util.sortBy,
            sortExpiry: util.sortExpiry,
            tblVal: util.tblVal,

            certIssuer: util.certIssuer,
            getCertHostPorts: util.getCertHostPorts,
            getCountCategoryLabelTbl: Req.del(util.getCountCategoryLabelTbl, util),

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

            //
            // Data processing
            //

            loadData(){
                const onFail = () => {
                    this.loadingState = -1;
                    toastr.error('Error while loading, please, try again later', 'Error');
                };

                const onSuccess = data => {
                    this.loadingState = 1;
                    this.results = data;
                    setTimeout(this.processData, 0);
                };

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
                        console.log('Add server failed: ' + e);
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
                this.$nextTick(() => {
                    console.log('Data loaded');
                    this.dataProcessStart = moment();
                    this.processResults();
                });
            },

            processResults() {
                util.processResults(this.results);

                this.$set(this.results, 'certificates', this.results.certificates);
                this.$forceUpdate();
                this.$emit('onProcessed');
                this.loadingState = 10;

                this.$nextTick(() => {
                    this.graphDataReady = true;
                    this.renderCharts();
                    this.postLoad();
                    const processTime = moment().diff(this.dataProcessStart);
                    console.log('Processing finished in ' + processTime + ' ms');
                });
            },

            postLoad(){

            },

            cleanResults(){
                this.results = null;
                this.loadingState = 0;
                this.$emit('onReset');
            },

            //
            // Graphs
            //

            renderCharts(){
                if (!this.graphDataReady){
                    return;
                }

                this.graphsRendered = true;
            },
        }
    }
</script>

<style>

</style>

