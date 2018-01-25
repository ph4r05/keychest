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
                            <h3>{{ len(tlsCerts) }} / {{ numHiddenCerts + len(tlsCerts) }}</h3>

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
                    <div class="small-box bg-aqua" v-if="len(dnsFailedLookups) + len(tlsErrors) < 1">
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
                            <h3>{{ len(dnsFailedLookups) + len(tlsErrors) }} / {{ numWatches }}</h3>
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

            <div class="row">
                <!-- left-hand side selection box -->
                <div class="col-lg-3 col-xs-6" style="background-color: white;">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Regulated</h3>
                            <div class="box-tools pull-right">
                                <!-- Buttons, labels, and many other things can be placed here! -->
                                <!-- Here is a label for example -->
                                <span class="label label-primary">LoA 5</span>
                            </div>
                            <!-- /.box-tools -->
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            100 / 2 groups
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                            MOC <div class="led-red">&nbsp;</div> /
                            WoC <div class="led-yellow">&nbsp;</div>
                        </div>
                        <!-- box-footer -->
                    </div>
                    <!-- /.box -->
                    <div class="btn-group">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Destination filter</button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#">Issuing CA</a>
                            <div class="dropdown-divider"></div> <!-- this shouldn't be needed once CSS is imported -->
                            <a class="dropdown-item" href="#">Owner</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#">Security group</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#">Key algorithm</a>
                        </div>
                    </div>

                    <div class="row">
                        <br>
                        <h5 style="text-align: center">List of "selected filter"</h5>
                    </div>
                    <div class="col-lg-12" style="padding-left:5px;padding-right:5px">
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h4 class="box-title">253 / 1 group</h4>
                                <p>Filter value A</p>
                                <!-- /.box-tools -->
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /.box -->

                        <div class="small-box bg-gray">
                            <div class="inner">
                                <h4 class="box-title">10 / 3 groups</h4>
                                <p>Filter value B</p>
                                <!-- /.box-tools -->
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /.box -->

                        <div class="small-box bg-gray">
                            <div class="inner">
                                <h4 class="box-title">23 / 5 groups</h4>
                                <p>Filter value C</p>
                                <!-- /.box-tools -->
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /.box -->
                    </div>
                </div>
                <!-- main list of LoAs and the destionation table -->
                <div class="col-lg-9 col-xs-6" style="overflow-x: auto; white-space: nowrap;">
                    <div class="row">
                    <div class="col-lg-3 col-xs-6" style="padding-left:5px;padding-right:5px">
                        <div class="box">
                            <div class="box-header with-border">
                                    <h3 class="box-title">Internal</h3>
                                <div class="box-tools pull-right">
                                    <!-- Buttons, labels, and many other things can be placed here! -->
                                    <!-- Here is a label for example -->
                                    <span class="label label-primary">LoA 1</span>
                                </div>
                                <!-- /.box-tools -->
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                253 / 10 groups
                            </div>
                            <!-- /.box-body -->
                            <div class="box-footer">
                                MOC <div class="led-red">&nbsp;</div> /
                                WoC <div class="led-yellow">&nbsp;</div>
                            </div>
                            <!-- box-footer -->
                        </div>
                        <!-- /.box -->

                    </div>
                    <div class="col-lg-3 col-xs-6" style="padding-left:5px;padding-right:5px">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">Internal HS</h3>
                                <div class="box-tools pull-right">
                                    <!-- Buttons, labels, and many other things can be placed here! -->
                                    <!-- Here is a label for example -->
                                    <span class="label label-primary">LoA 2</span>
                                </div>
                                <!-- /.box-tools -->
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                333 / 10 groups
                            </div>
                            <!-- /.box-body -->
                            <div class="box-footer">
                                MOC <div class="led-red">&nbsp;</div> /
                                WoC <div class="led-yellow">&nbsp;</div>
                            </div>
                            <!-- box-footer -->
                        </div>
                        <!-- /.box -->
                    </div>
                    <div class="col-lg-3 col-xs-6" style="padding-left:5px;padding-right:5px">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">External</h3>
                                <div class="box-tools pull-right">
                                    <!-- Buttons, labels, and many other things can be placed here! -->
                                    <!-- Here is a label for example -->
                                    <span class="label label-primary">LoA 3</span>
                                </div>
                                <!-- /.box-tools -->
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                3233 / 34 groups
                            </div>
                            <!-- /.box-body -->
                            <div class="box-footer">
                                MOC <div class="led-red">&nbsp;</div> /
                                WoC <div class="led-yellow">&nbsp;</div>
                            </div>
                            <!-- box-footer -->
                        </div>
                        <!-- /.box -->
                    </div>
                    <div class="col-lg-3 col-xs-6" style="padding-left:5px;padding-right:5px">
                        <div class="box gb-gray">
                            <div class="box-header with-border">
                                <h3 class="box-title">External HS</h3>
                                <div class="box-tools pull-right">
                                    <!-- Buttons, labels, and many other things can be placed here! -->
                                    <!-- Here is a label for example -->
                                    <span class="label label-primary">LoA 4</span>
                                </div>
                                <!-- /.box-tools -->
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                333 / 10 groups
                            </div>
                            <!-- /.box-body -->
                            <div class="box-footer">
                                MOC <div class="led-gray">&nbsp;</div> /
                                WoC <div class="led-gray">&nbsp;</div>
                            </div>
                            <!-- box-footer -->
                        </div>
                        <!-- /.box -->
                    </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-7">
                            <h5 style="color:#00a7d7">Regulated -> Filter value A</h5>
                            <p>The group's description - this is a very important group.</p>
                            <button target="_blank" type="button" class="button button-aqua">Add New Destination</button>

                        </div>
                        <div class="col-lg-5">
                            <p>Group owner: John Jones</p>
                            <button target="_blank" type="button" class="button button-aqua">Manage Owners</button>
                            <button target="_blank" class="button button-aqua">View History Log</button>
                        </div>
                    </div>
                    <div class="row" style="background-color: white">
                        <div class="table-responsive table-wrapper" ref="tbl">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <td>Business</td>
                                    <th>Collective</th>
                                    <th>Destinations</th>
                                    <th>Deployment</th>
                                    <th>Creation date</th>
                                    <th>Last failure</th>
                                    <th>Status</th>
                                    <th>Change/Details</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>Trading platform</td>
                                    <td>URL/name</td>
                                    <td>
                                        2
                                    </td>
                                    <td>
                                        Agent ID - DDD
                                    </td>
                                    <td>date created</td>
                                    <td>date scanned</td>
                                    <td>RAG/status</td>
                                    <td>btn</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
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

            <!-- Section heading - INCIDENTS -->
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
            <expiring-domains :whois="whois"/>

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
                graphsRendered: false,

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

<style scoped>
/*
    Source:

    https://bootsnipp.com/snippets/3M05b

   it also includes sass styles

 */
    .led-red,
    .led-red-blink,
    .led-green,
    .led-green-blink,
    .led-yellow,
    .led-gray,
    .led-gray-blink,
    .led-yellow-blink {
        margin: 0 auto;
        padding-right: 5px;
        width: 1.5em;
        height: 1.5em;
        border-radius: 50%;
        display: inline-block;
    }
    .led-gray,
    .led-gray-blink {
        background-color: #aab2bd;
        box-shadow: #aab2bd 0 -1px 6px 1px, inset #656d78 0 -1px 8px, #656d78 0 3px 11px;
    }
    .led-red,
    .led-red-blink {
        background-color: #dd4b39;
        box-shadow: #7D7B80 0 -1px 6px 1px, inset #600 0 -1px 8px, #dd4b39 0 3px 11px;
    }
    .led-green,
    .led-green-blink {
        background-color: #00a65a;
        box-shadow: #7D7B80 0 -1px 6px 1px, inset #460 0 -1px 8px, #00a65a 0 3px 11px;
    }

    .led-yellow,
    .led-yellow-blink {
        background-color: #f39c12;
        box-shadow: #7D7B80 0 -1px 6px 1px, inset #660 0 -1px 8px, #f39c12 0 3px 11px;
    }

    @-moz-keyframes blinkRed {
        0% {
            background-color: #db212b;
        }
        50% {
            background-color: #db212b;
            box-shadow: rgba(0, 0, 0, 0.2) 0 -1px 6px 1px, inset #441313 0 -1px 8px, #db212b 0 1px 0;
        }
        100% {
            background-color: #db212b;
        }
    }
    @-webkit-keyframes blinkRed {
        0% {
            background-color: #db212b;
        }
        50% {
            background-color: #db212b;
            box-shadow: rgba(0, 0, 0, 0.2) 0 -1px 6px 1px, inset #441313 0 -1px 8px, #db212b 0 1px 0;
        }
        100% {
            background-color: #db212b;
        }
    }
    @keyframes blinkRed {
        0% {
            background-color: #db212b;
        }
        50% {
            background-color: #db212b;
            box-shadow: rgba(0, 0, 0, 0.2) 0 -1px 6px 1px, inset #441313 0 -1px 8px, #db212b 0 1px 0;
        }
        100% {
            background-color: #db212b;
        }
    }
    @-moz-keyframes blinkYellow {
        0% {
            background-color: #FF0;
        }
        50% {
            background-color: #AA0;
            box-shadow: rgba(0, 0, 0, 0.2) 0 -1px 6px 1px, inset #441313 0 -1px 8px, #FF0 0 1px 0;
        }
        100% {
            background-color: #FF0;
        }
    }
    @-webkit-keyframes blinkYellow {
        0% {
            background-color: #FF0;
        }
        50% {
            background-color: #AA0;
            box-shadow: rgba(0, 0, 0, 0.2) 0 -1px 6px 1px, inset #441313 0 -1px 8px, #FF0 0 1px 0;
        }
        100% {
            background-color: #FF0;
        }
    }
    @keyframes blinkYellow {
        0% {
            background-color: #FF0;
        }
        50% {
            background-color: #AA0;
            box-shadow: rgba(0, 0, 0, 0.2) 0 -1px 6px 1px, inset #441313 0 -1px 8px, #FF0 0 1px 0;
        }
        100% {
            background-color: #FF0;
        }
    }
    @-moz-keyframes blinkGreen {
        0% {
            background-color: #80FF00;
        }
        50% {
            background-color: #6cff82;
            box-shadow: rgba(0, 0, 0, 0.2) 0 -1px 6px 1px, inset #441313 0 -1px 8px, #80FF00 0 1px 0;
        }
        100% {
            background-color: #80FF00;
        }
    }
    @-webkit-keyframes blinkGreen {
        0% {
            background-color: #80FF00;
        }
        50% {
            background-color: #6cff82;
            box-shadow: rgba(0, 0, 0, 0.2) 0 -1px 6px 1px, inset #441313 0 -1px 8px, #80FF00 0 1px 0;
        }
        100% {
            background-color: #80FF00;
        }
    }
    @keyframes blinkGreen {
        0% {
            background-color: #80FF00;
        }
        50% {
            background-color: #6cff82;
            box-shadow: rgba(0, 0, 0, 0.2) 0 -1px 6px 1px, inset #441313 0 -1px 8px, #80FF00 0 1px 0;
        }
        100% {
            background-color: #80FF00;
        }
    }
    .led-red-blink {
         -webkit-animation: blinkRed 0.7s infinite;
         -moz-animation: blinkRed 0.7s infinite;
         -ms-animation: blinkRed 0.7s infinite;
         -o-animation: blinkRed 0.7s infinite;
         animation: blinkRed 0.7s infinite;
     }

    .led-yellow-blink {
        -webkit-animation: blinkYellow 0.7s infinite;
        -moz-animation: blinkYellow 0.7s infinite;
        -ms-animation: blinkYellow 0.7s infinite;
        -o-animation: blinkYellow 0.7s infinite;
        animation: blinkYellow 0.7s infinite;
    }

    .led-green-blink {
        -webkit-animation: blinkGreen 0.7s infinite;
        -moz-animation: blinkGreen 0.7s infinite;
        -ms-animation: blinkGreen 0.7s infinite;
        -o-animation: blinkGreen 0.7s infinite;
        animation: blinkGreen 0.7s infinite;
    }

</style>

