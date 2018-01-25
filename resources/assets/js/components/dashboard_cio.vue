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

                <!-- headlines candidates:
                servers down
                certificates expired
                server configuration errors
                work load - certificates / resources - month on month and year on year
                RAGs
                -->

                <!-- HEADLINE: business functions' status -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-gray">
                        <div class="inner">
                            <h3>N/A&#37; <span style="font-size: smaller">of 0</span> </h3>
                            <p>Business functions</p>
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
                    <div class="small-box bg-green" v-if="len(dnsFailedLookups) + len(tlsErrors) < 1 ">
                        <div class="inner">
                            <h3>100&#37; <span style="font-size: smaller">of {{ numWatches }}</span></h3>
                            <p>Security groups</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-server"></i> <!--fa-sitemap-->
                        </div>
                        <!--suppress HtmlUnknownTarget -->
                        <a href="/home/servers" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>

                    <div class="small-box bg-yellow" v-else="">
                        <div class="inner">
                            <h3>{{ percSecurityGroup }}&#37;
                                <span style="font-size: smaller">of {{ numWatches }}</span></h3>
                            <p>Security groups</p>
                        </div>

                        <div class="icon">
                            <i class="fa fa-server"></i> <!--fa-sitemap-->
                        </div>
                        <!--suppress HtmlUnknownTarget -->
                        <a href="/home/servers" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->

                <!-- HEADLINE: certificates expire now -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box &ndash;&gt;-->
                    <div class="small-box"
                         v-bind:class="{'bg-green': numExpiresNow < 1, 'bg-red': numExpiresNow > 0}" >
                        <div class="inner">
                            <h3>{{ percExpiresNow }}&#37; <span style="font-size: smaller">of {{ len(certs) }}</span> </h3>
                            <p>{{ pluralize('Certificate', numExpiresNow) }} {{ pluralize('expire', numExpiresNow) }} now</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-exclamation-circle"></i>
                        </div>
                        <a href="#renewals" class="small-box-footer"
                           v-if="numExpiresNow > 0">More info <i class="fa fa-arrow-circle-right"></i></a>
                        <a href="https://www.tripadvisor.co.uk/Search?geo=&latitude=&longitude=&searchNearby=&redirect=&startTime=&uiOrigin=&q=short+breaks"
                           v-else-if="numExpiresSoon > 0"
                           target="_blank"
                           class="small-box-footer">Take a short break <i class="fa fa-arrow-circle-right"></i></a>
                        <a href="#" class="small-box-footer" v-else="">This looks good</a>
                    </div>
                </div>
                <!-- ./col -->

                <!-- HEADLINE: workload - this month compared to last month -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-green" v-if="0 < 1">
                        <div class="inner">
                            <h3>&uarr;{{ 30 }}&#37; </h3>
                            <p>Workload change - month</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-server"></i> <!--fa-sitemap-->
                        </div>
                        <!--suppress HtmlUnknownTarget -->
                        <a href="/home/servers" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>

                    <div class="small-box bg-yellow" v-else="">
                        <div class="inner">
                            <h3>&uarr;{{ 31 }}&#37; </h3>
                            <p>Workload change - month</p>
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
                    <div class="info-box-content">
                        <span class="info-box-number">Security Business Functions Report</span>
                        <span class="info-box-text">{{ curDateUsString() }}</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 70%"></div>
                        </div>
                        <span class="progress-description">NA&#37; business functions operational</span>
                    </div>
                </div>
                <p class="tc-onyx">This dashboard contains latest available information for your servers and certificates. If you've
                made recent changes to some of your servers and these are not yet reflected in the dashboard, please use
                    the Spot Check function to get the real-time status.<br><br></p>

            </div>

            <!-- Monthly planner -->
            <cert-planner-cio
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

            <a name="operationalStatusBusiness"></a>
            <div class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-success" :headerCollapse="true">
                        <template slot="title">Operational RAG per Business Function</template>
                        <p>The table shows the operational summary per business function.
                        </p>
                    </sbox>
                </div>
            </div>

            <a name="operationalStatusSecGroup"></a>
            <div class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-success" :headerCollapse="true">
                        <template slot="title">Operational RAG per Security Group</template>
                        <p>The table shows the operational summary per security group.
                        </p>
                    </sbox>
                </div>
            </div>

            <!-- Section heading -->
            <incidents
                    :dns-failed-lookups="dnsFailedLookups"
                    :tls-errors="tlsErrors"
                    :expired-certificates="expiredCertificates"
                    :tls-invalid-trust="tlsInvalidTrust"
                    :tls-invalid-hostname="tlsInvalidHostname"
            />

            <!-- Section heading - INFORMATIONAL -->
            <div class="row">
                <div class="info-box">
                    <span class="info-box-icon bg-blue"><i class="fa fa-info-circle"></i></span>
                    <div class="info-box-content info-box-label">
                        Informational
                    </div>
                </div>
            </div>

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
    import toastr from 'toastr';
    import pluralize from 'pluralize';

    import Psl from 'ph4-psl';
    import Req from 'req';
    import util from './dashboard/code/util';

    import VeeValidate from 'vee-validate';
    import ToggleButton from 'vue-js-toggle-button';

    import Vue from 'vue';

    import CertsMixin from './dashboard/code/certsMix';
    import DashboardStatsMixin from './dashboard/code/dashboardStatsMix';
    import DashboardFailsMixin from './dashboard/code/dashboardFailsMix';

    import DashboardCertPlanner from './dashboard/CertPlanner';
    import DashboardCertPlannerCio from './dashboard/CertPlannerCio';
    import DashboardIncidentSummary from './dashboard/IncidentSummary';
    import DashboardIncidents from './dashboard/Incidents';
    import DashboardCertAllList from './dashboard/CertAllList';

    import './dashboard/css/dashboard.css';

    Vue.use(ToggleButton);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    export default {
        mixins: [
            CertsMixin,
            DashboardStatsMixin,
            DashboardFailsMixin,
        ],

        components: {
            'cert-planner': DashboardCertPlanner,
            'cert-planner-cio': DashboardCertPlannerCio,
            'incident-summary': DashboardIncidentSummary,
            'incidents': DashboardIncidents,
            'all-certs': DashboardCertAllList,
        },

        data: function() {
            return {
                loadingState: 0,
                results: null,
                dataProcessStart: null,

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
            hasAccount() {
                return !this.Laravel.authGuest;
            },

            percSecurityGroup() {
                return 100 - Math.round(100. * (this.len(this.dnsFailedLookups) + this.len(this.tlsErrors)) / this.numWatches);
            },

            percExpiresNow() {
                return Math.round(100. * this.numExpiresNow / this.len(this.certs));
            },
        },

        methods: {
            hookup(){
                setTimeout(this.loadData, 0);
            },

            //
            // Utility / helper functions / called from template directly
            //

            pluralize,

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
                setTimeout(() => util.pslWarmUp(Psl), 10);
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
        }
    }
</script>

<style>

</style>

