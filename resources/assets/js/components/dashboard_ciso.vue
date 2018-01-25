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
                <span>Processing data...</span>
            </div>

            <div class="alert alert-success scan-alert" id="search-success" style="display: none">
                <strong>Success!</strong> Scan finished.
            </div>
        </div>

        <transition name="fade" v-on:after-leave="transition_hook">
        <div v-if="loadingState === 10">

            <!-- Header info widgets -->
            <div class="row">
                <!-- HEADLINE: external certificate compliance -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box"
                         v-bind:class="{'bg-green': len(certs) > 1, 'bg-red': len(certs) < 1}" >
                        <div class="inner">
                            <h3>100% <span style='font-size: smaller;'>of {{ len(certs) }}</span></h3>

                            <p>External compliance</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-exclamation-circle"></i>
                        </div>
                        <a href="#compliance_ext" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->

                <!-- HEADLINE: internal certificate compliance -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box"
                         v-bind:class="{'bg-gray': 0 < 1, 'bg-green': 0 > 0}" >
                        <div class="inner">
                            <h3>N/A <span style='font-size: smaller;'>of 0</span></h3>
                            <p>Internal compliance</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-bell"></i>
                        </div>
                        <a href="#compliance_int" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->


                <!-- HEADLINE: TLS/network compliance -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box bg-red or bg-yellow or bg-green -->
                    <div class="small-box"
                         v-bind:class="{
                          'bg-green': len(tlsNonCompliance) < 1,
                          'bg-yellow': len(tlsNonCompliance) * 10 < len(tls),
                          'bg-red': len(tlsNonCompliance) * 10 >= len(tls)}" >
                        <div class="inner">
                            <h3>{{ percNetworkCompliance }}% <span style='font-size: smaller;'>of {{ len(tls) }}</span></h3>
                            <p>Network compliance</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-server"></i> <!--fa-sitemap-->
                        </div>
                        <!--suppress HtmlUnknownTarget -->
                        <a href="#compliance_tls" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->

                <!-- HEADLINE: coverage -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box"
                         v-bind:class="{
                            'bg-green': len(tlsCerts) > 0.9*len(certs),
                            'bg-yellow': len(tlsCerts) > 0.7*len(certs),
                            'bg-red': 1 > 0}" >
                        <div class="inner">
                            <h3>{{ percGovernanceUse }}% / {{ percGovernanceAll }}%</h3>
                            <p>Governance (use / all)</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-exclamation-circle"></i>
                        </div>
                        <a href="#coverage" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->

            </div>

            <!-- Section heading -->
            <div class="row">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-tachometer"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-number">Cyber Security Compliance Report </span>
                        <span class="info-box-text">{{ curDateUsString() }}</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 70%"></div>
                        </div>
                        <span class="progress-description">{{ percCompliance }}% compliance
    </span>
                    </div>
                </div>
                <p class="tc-onyx">This dashboard contains latest available information for your servers and certificates. If you've
                made recent changes to some of your servers and these are not yet reflected in the dashboard, please use
                    the Spot Check function to get the real-time status.<br><br></p>

            </div>

            <a name="noncomplianceHistory"></a>
            <div class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-success" :headerCollapse="true">
                        <template slot="title">Compliance over the last 12 months</template>
                        <p>The table shows the compliance summary offer the last 12 months. TODO
                        </p>
                    </sbox>
                </div>
            </div>

            <!-- incident summary table -->
            <a name="noncomplianceSummary"></a>
            <div class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-success" :headerCollapse="true">
                        <template slot="title">Non-compliance incidents</template>
                            <p>The table shows a summary of identified issues per category.
                            </p>
                        <div class="table-responsive table-xfull">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <!--<th>ID</th>-->
                                    <th>Non-compliance category</th>
                                    <th>Number of  incidents</th>
                                </tr>
                                </thead>

                                <tbody>

                                <tr>
                                <td>Non-compliant TLS version</td>
                                <td>{{ len(tlsNonCompliance) }}</td>
                                </tr>

                                <tr>
                                <td>Incorrect certificates</td>
                                <td>{{ len(tlsInvalidHostname) }}</td>
                                </tr>

                                <tr>
                                    <td>Non-compliant issuer</td>
                                    <td>0</td>
                                </tr>

                                </tbody>
                            </table>
                        </div>
                    </sbox>
                </div>
            </div>

            <a name="noncomplianceRiskGroup"></a>
            <div class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-success" :headerCollapse="true">
                        <template slot="title">Compliance RAG per Risk Group</template>
                        <p>The table shows the compliance summary per security group.
                        </p>
                    </sbox>
                </div>
            </div>
            
            <!-- Section heading - INCIDENTS -->
            <incidents
                    :tls-invalid-trust="tlsInvalidTrust"
                    :tls-invalid-hostname="tlsInvalidHostname"
            >
                <template slot="title">Non-compliance details</template>
            </incidents>

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
    import DashboardCertIssuers from './dashboard/CertIssuers';
    import DashboardCertDomains from './dashboard/CertDomains';
    import DashboardCertRenewals from './dashboard/CertRenewals';
    import DashboardCertTypes from './dashboard/CertTypes';
    import DashboardIncidentSummary from './dashboard/IncidentSummary';
    import DashboardIncidents from './dashboard/Incidents';
    import DashboardExpiringDomains from './dashboard/ExpiringDomains';
    import DashboardCertTlsList from './dashboard/CertTlsList';
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
            'cert-issuers': DashboardCertIssuers,
            'cert-domains': DashboardCertDomains,
            'cert-types': DashboardCertTypes,
            'imminent-renewals': DashboardCertRenewals,
            'incident-summary': DashboardIncidentSummary,
            'incidents': DashboardIncidents,
            'expiring-domains': DashboardExpiringDomains,
            'tls-certs': DashboardCertTlsList,
            'all-certs': DashboardCertAllList,
        },

        data: function() {
            return {
                loadingState: 0,
                results: null,
                dataProcessStart: null,
                graphDataReady: false,

                includeExpired: false,
                includeNotVerified: false,

                Laravel: window.Laravel,

                expiringNowDays: 7,
                expiringSoonDays: 28
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

            percNetworkCompliance() {
                return Math.round(100 * (this.len(this.tls) - this.len(this.tlsNonCompliance)) / this.len(this.tls));
            },

            percGovernanceUse() {
                return Math.round(100 * (this.len(this.tls) - this.len(this.tlsErrors)) / this.len(this.tls));
            },

            percGovernanceAll() {
                return Math.round(100 * (this.len(this.certs) - this.len(this.tlsCerts)) / this.len(this.certs));
            },

            percCompliance() {
                return Math.round(100 * (this.len(this.tls) - this.len(this.tlsErrors)) / this.len(this.tls))
                     * Math.round((this.len(this.certs) - this.len(this.tlsCerts)) / this.len(this.certs));
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
                    this.graphDataReady = true;
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
