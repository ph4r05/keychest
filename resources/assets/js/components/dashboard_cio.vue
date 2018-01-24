<template>
    <div class="dashboard-wrapper">
        <div class="row">
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
            <!-- X TLS certificate expired notices - last attempt -->
            <!--   TLS certificates trust problems (self signed, is_ca, empty chain, generic, HOSTNAME validation error) -->
            <!--   TLS certificate changes over time on the IP -->
            <!--   connection stats, small inline graphs? like status -->
            <!-- X Whois domain expiration notices -->
            <!--   CT only certificates to a table + chart -->
            <!--     how to detect CT only? was detected at some point? at some scan? new DB table for watch <-> cert assoc ? -->

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
                    <div class="small-box bg-green" v-if="dnsFailedLookups.length+ tlsErrors.length < 1 ">
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
                            <h3>{{100 - Math.round(100*(dnsFailedLookups.length + tlsErrors.length)/numWatches) }}&#37;
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
                            <h3>{{ Math.round(100*numExpiresNow/len(certs)) }}&#37; <span style="font-size: smaller">of {{len(certs)}}</span> </h3>
                            <p>{{pluralize('Certificate', numExpiresNow)}} {{pluralize('expire', numExpiresNow)}} now</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-exclamation-circle"></i>
                        </div>
                        <a href="#renewals" class="small-box-footer"
                           v-if="numExpiresNow > 0">More info <i class="fa fa-arrow-circle-right"></i></a>
                        <a href="https://www.tripadvisor.co.uk/Search?geo=&latitude=&longitude=&searchNearby=&redirect=&startTime=&uiOrigin=&q=short+breaks"
                           v-else-if="numExpiresSoon>0"
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
                            <h3>&uarr;{{30}}&#37; </h3>
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
                            <h3>&uarr;{{31}}&#37; </h3>
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
                        <span class="info-box-text">{{ (new Date()).toLocaleString("en-us",{'day':'numeric','month':'short',
                            'year':'numeric', 'hour':'numeric','minute':'numeric'}) }}</span>
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
            <div class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-success" :headerCollapse="true">
                        <template slot="title">Operational status - 12 months</template>
                        <p>
                            A chart of the operational status of business functions,
                            and destinations.
                            <br>
                            <i>Note: you can click an chart labels to hide/unhide information.</i>
                        </p>
                        <div class="form-group">
                            <canvas id="columnchart_certificates_js" style="width:100%; height: 350px"></canvas>
                        </div>
                    </sbox>
                </div>
            </div>

            <div class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-success" :headerCollapse="true">
                        <template slot="title">Certificate expiration levels - 12 months</template>
                        <p>A chart of the level of expired certificates over the last 12 months.
                            <br>
                            <i>Note: you can click an chart labels to hide/unhide information.</i></p>
                        <div class="form-group">
                            <canvas id="columnchart_certificates_all_js" style="width:100%; height: 350px"></canvas>
                        </div>

                    </sbox>
                </div>
            </div>

            <!-- incident summary table -->
            <a name="incidentSummary"></a>
            <div class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-success" :headerCollapse="true">
                        <template slot="title">Number of incidents per category</template>
                            <p>The table shows a summary of the number of active incidents per category.
                            Futher details are in the "Incidents" section of the dashboard.
                            </p>
                        <div class="table-responsive table-xfull">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <!--<th>ID</th>-->
                                    <th>Incident category</th>
                                    <th>Number of active incidents</th>
                                </tr>
                                </thead>

                                <tbody>
                                <tr>
                                <td>DNS configuration issues</td>
                                <td>{{dnsFailedLookups.length}}</td>
                                </tr>

                                <tr>
                                <td>Unreachable servers</td>
                                <td>{{tlsErrors.length}}</td>
                                </tr>

                                <tr>
                                <td>Servers with configuration errors</td>
                                <td>{{len(tlsInvalidTrust)}}</td>
                                </tr>

                                <tr>
                                <td>Incorrect certificates</td>
                                <td>{{len(tlsInvalidHostname)}}</td>
                                </tr>

                                <tr>
                                <td>Expired certificates</td>
                                <td>{{len(expiredCertificates)}}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </sbox>
                </div>
            </div>

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
            <div class="row" v-if="
                    dnsFailedLookups.length > 0 ||
                    tlsErrors.length > 0 ||
                    len(expiredCertificates) > 0 ||
                    len(tlsInvalidTrust) > 0 ||
                    len(tlsInvalidHostname) > 0
                ">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-exclamation-circle"></i></span>
                    <div class="info-box-content info-box-label">
                        Incidents
                    </div>
                </div>
            </div>

            <!-- DNS lookup fails -->
            <div v-if="tlsErrors.length > 0" class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-danger" :collapsed="true" :headerCollapse="true">
                        <template slot="title">DNS errors ({{dnsFailedLookups.length}})</template>
                        <p>Please check if the following domain names are correct. You may also need to verify
                            your DNS configuration at your DNS registrar and at your DNS servers.</p>
                        <div class="table-responsive table-xfull">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>Domain name</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="dns in dnsFailedLookups" class="danger">
                                    <td>
                                        <span class="hidden">
                                            ID: {{ dns.id }}
                                        </span>
                                        {{ dns.domain }}
                                    </td>
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
                    <sbox cssBox="box-danger" :collapsed="true" :headerCollapse="true">
                        <template slot="title">Unreachable servers ({{tlsErrors.length}})</template>

                        <p>We failed to connect to one or more servers using TLS protocol.</p>
                        <div class="table-responsive table-xfull">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>Server name</th>
                                    <th>Address</th>
                                    <th>Cause</th>
                                    <th>Time of detection</th>
                                    <th>Last failure</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="tls in tlsErrors" class="danger">
                                    <td>
                                        <span class="hidden">
                                            ID: {{ tls.id }}
                                        </span>
                                        {{ tls.url_short }}
                                    </td>
                                    <td>{{ tls.ip_scanned }}</td>
                                    <td>
                                        <span v-if="tls.err_code == 1">TLS handshake error</span>
                                        <span v-else-if="tls.err_code == 2">No server detected</span>
                                        <span v-else-if="tls.err_code == 3">Timeout</span>
                                        <span v-else-if="tls.err_code == 4">Domain lookup error</span>
                                        <span v-else="">TLS/SSL not present</span>
                                    </td>
                                    <td>{{ new Date(tls.created_at_utc * 1000.0).toLocaleString() }}
                                         ({{ momentu(tls.created_at_utc * 1000.0).fromNow() }})</td>
                                    <td>{{ new Date(tls.last_scan_at_utc * 1000.0 ).toLocaleString() }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </sbox>
                </div>
            </div>

            <!-- TLS trust errors -->
            <div v-if="len(tlsInvalidTrust) > 0" class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-danger" :collapsed="true" :headerCollapse="true">
                        <template slot="title">Servers with configuration errors ({{ len(tlsInvalidTrust) }})</template>
                        <p>We detected security or configuration problems at following servers</p>
                        <div class="table-responsive table-xfull">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>Server name</th>
                                    <th>Address</th>
                                    <th>Cause</th>
                                    <th>Time of detection</th>
                                    <th>Last failure</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="tls in sortBy(tlsInvalidTrust, 'created_at_utc')" class="danger">
                                    <td>
                                        <span class="hidden">
                                            ID: {{ tls.id }}
                                        </span>
                                        {{ tls.url_short }}
                                    </td>
                                    <td>{{ tls.ip_scanned }}</td>
                                    <td>
                                        <ul class="domain-list">
                                            <li v-if="tls.host_cert && tls.host_cert.is_self_signed">Self-signed certificate</li>
                                            <li v-if="tls.host_cert && tls.host_cert.is_ca">CA certificate</li>
                                            <li v-if="tls.host_cert && len(tls.certs_ids) > 1">Validation failed</li>
                                            <li v-else-if="len(tls.certs_ids) === 1">Incomplete trust chain</li>
                                            <li v-else-if="len(tls.certs_ids) === 0">No certificate</li>
                                            <li v-else-if="tls.host_cert">Untrusted certificate</li>
                                            <li v-else="">No host certificate</li>
                                        </ul>
                                    </td>
                                    <td>{{ new Date(tls.created_at_utc * 1000.0).toLocaleString() }}
                                        ({{ momentu(tls.created_at_utc * 1000.0).fromNow() }})</td>
                                    <td>{{ new Date(tls.last_scan_at_utc * 1000.0).toLocaleString() }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </sbox>
                </div>
            </div>

            <!-- TLS hostname errors -->
            <div v-if="len(tlsInvalidHostname) > 0" class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-danger" :collapsed="true" :headerCollapse="true">
                        <template slot="title">Unused, default, or incorrect certificates ({{len(tlsInvalidHostname)}})</template>
                        <p>Service name (URL) is different from the name in certificates</p>
                        <div class="table-responsive table-xfull">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>Server name</th>
                                    <th>Name(s) in certificate</th>
                                    <th>Time of detection</th>
                                    <th>Last failure</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="tls in sortBy(tlsInvalidHostname, 'created_at_utc')" class="danger">
                                    <td><span class="hidden">
                                            ID: {{ tls.id }}
                                        </span>
                                        {{ tls.url_short }}
                                    </td>
                                    <td>
                                        <ul class="coma-list" v-if="tls.host_cert">
                                            <li v-for="domain in take(tls.host_cert.alt_domains, 10)">{{ domain }}</li>
                                        </ul>
                                        <span v-else="">No domains found</span>
                                    </td>
                                    <td>{{ new Date(tls.created_at_utc * 1000.0).toLocaleString() }}
                                        ({{ momentu(tls.created_at_utc * 1000.0).fromNow() }})</td>
                                    <td>{{ new Date(tls.last_scan_at_utc * 1000.0).toLocaleString() }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </sbox>
                </div>
            </div>

            <!-- TLS expired certificates -->
            <div v-if="len(expiredCertificates) > 0" class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-danger" :headerCollapse="true">
                        <template slot="title">Servers with expired certificates ({{len(expiredCertificates)}})</template>
                        <p>Clients can't connect to following servers due to expired certificates.</p>
                        <div class="table-responsive table-xfull">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>Server name</th>
                                    <th>Certificate issuers</th>
                                    <th>Expiration date</th>
                                    <th>Last failure</th>
                                    <!--<th>ID</th> -->
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="cert in sortBy(expiredCertificates, 'expires_at_utc')" class="danger">
                                    <td>
                                        <span class="hidden">
                                            ID: {{ cert.id }}
                                        </span>
                                        <ul class="domain-list">
                                            <li v-for="domain in cert.watch_hosts">
                                                <template v-if="cert.cname === domain">{{ domain }} <small><em>(CN)</em></small></template>
                                                <template v-else="">{{ domain }}</template>
                                            </li>
                                        </ul>
                                    </td>
                                    <td>{{ cert.issuerOrgNorm }}</td>
                                    <td>{{ new Date(cert.valid_to_utc * 1000.0).toLocaleString() }}
                                        ({{ momentu(cert.valid_to_utc * 1000.0).fromNow() }})</td>
                                    <td>{{ new Date(cert.last_scan_at_utc * 1000.0).toLocaleString() }}</td>
                                    <!--<td>{{ cert.id }}</td>-->
                                </tr>
                                </tbody>
                            </table>
                        </div>
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

            <!-- All Certificate list -->
            <a name="allCerts"></a>
            <div class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-primary" :collapsed="true" :headerCollapse="true">
                        <template slot="title">All certificates of your servers</template>
                        <div class="form-group">
                            <p>The list shows all certificates in Certificate Transparency (CT) public logs ({{ len(certs) }}).</p>
                            <toggle-button v-model="includeExpired" id="chk-include-expired"
                                           color="#00a7d7"
                                           :labels="{checked: 'On', unchecked: 'Off'}"
                            ></toggle-button>
                            <label for="chk-include-expired">Include expired CT certificates</label>
                        </div>
                        <div class="table-responsive table-xfull">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>Domain name(s)</th>
                                    <th>Issuer</th>
                                    <th>Source</th>
                                    <th colspan="2">Certificate expiration date</th>
                                </tr>
                                </thead>

                                <tbody>
                                <tr v-for="cert in sortExpiry(certs)" v-if="cert.planCss && (momentu(cert.valid_to)<momentu())">
                                    <td v-bind:class="cert.planCss.tbl">
                                        <span class="hidden">
                                            ID: {{ cert.id }}
                                            CNAME: {{ cert.cname }}
                                        </span>
                                        <ul class="domain-list">
                                            <li v-for="domain in cert.watch_hosts_ct">
                                                <template v-if="cert.cname === domain">{{ domain }} <small><em>(CN)</em></small></template>
                                                <template v-else="">{{ domain }}</template>
                                            </li>
                                        </ul>
                                    </td>
                                    <td v-bind:class="cert.planCss.tbl">{{ cert.issuerOrgNorm }}</td>
                                    <td v-bind:class="cert.planCss.tbl">
                                        <span class="label label-success" title="TLS scan" v-if="len(cert.watch_hosts) > 0">TLS</span>
                                        <span class="label label-primary" title="CT scan" v-if="len(cert.watch_hosts_ct) > 0">CT</span>
                                    </td>
                                    <td v-bind:class="cert.planCss.tbl">{{ cert.valid_to }}</td>
                                    <td v-bind:class="cert.planCss.tbl"
                                        v-if="momentu(cert.valid_to)<momentu()">EXPIRED {{ momentu(cert.valid_to).fromNow() }}</td>
                                    <td v-bind:class="cert.planCss.tbl"
                                        v-else="">{{ momentu(cert.valid_to).fromNow() }}</td>
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
    import _ from 'lodash';
    import axios from 'axios';
    import moment from 'moment';
    import sprintf from 'sprintf-js';
    import Psl from 'ph4-psl';
    import Req from 'req';
    import ReqD from 'req-data';
    import util from './dashboard/util'
    import pluralize from 'pluralize';

    import VueCharts from 'vue-chartjs';
    import ToggleButton from 'vue-js-toggle-button';
    import { Bar, Line } from 'vue-chartjs';
    import Chart from 'chart.js';
    import toastr from 'toastr';

    import Vue from 'vue';

    Vue.use(ToggleButton);

    export default {
        data: function() {
            return {
                loadingState: 0,
                results: null,
                dataProcessStart: null,

                graphLibLoaded: false,
                graphsRendered: false,
                graphDataReady: false,

                certIssuerTableData: null,
                includeExpired: false,
                includeNotVerified: false,

                Laravel: window.Laravel,

                chartColors: [
                    '#00c0ef',
                    '#f39c12',
                    '#00a65a',
                    '#f56954',
                    '#3c8dbc',
                    '#d2d6de',
                    '#ff6384',
                    '#d81b60',
                    '#ffcd56',
                    '#4bc0c0',
                    '#36a2eb',
                    '#9966ff',
                    '#001F3F',
                    '#605ca8',
                    '#ffde56',
                    '#c43833',
                ],

                countCategories: [1,2,5,10,25,50,100,250,500,1000]
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

                const cdnCertsTls = _.map(_.filter(_.values(this.results.tls), tls => {
                    return !_.isEmpty(tls.cdn_cname) || !_.isEmpty(tls.cdn_headers) || !_.isEmpty(tls.cdn_reverse);
                }), tls => {
                    return tls.cert_id_leaf;
                });

                return Req.listToSet(_.uniq(_.union(cdnCertsTls,
                    _.map(_.filter(this.results.certificates, crt =>{
                        return crt.is_cloudflare;
                    }), crt => {
                        return crt.id;
                    })
                )));
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

            showImminentRenewals(){
                return _.reduce(this.tlsCerts, (acc, cur) => {
                    return (acc + (cur.valid_to_days <= 28 && cur.valid_to_days >= -28));
                }, 0) > 0;
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

            imminentRenewalCerts(){
                const imm = _.filter(this.tlsCerts, x => { return (x.valid_to_days <= 28 && x.valid_to_days >= -28) });
                const grp = _.groupBy(imm, x => {
                    return x.valid_to_dayfmt;
                });
                return _.sortBy(grp, [x => {return x[0].valid_to_days; }]);
            },

            crtTlsMonth(){
                return this.monthDataGen(_.filter(this.tlsCerts, o => {
                    return o.valid_to_days >= 0 && o.valid_to_days < 365; }));
            },

            crtAllMonth() {
                return this.monthDataGen(_.filter(this.certs, o => {
                    return o.valid_to_days >= 0 && o.valid_to_days < 365; }))
            },

            certTypesStats(){
                return this.certTypes(this.tlsCerts);
            },

            certTypesStatsAll(){
                return this.certTypes(this.certs);
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
                return _.sortBy(_.filter(this.tls, x => {
                    return x && x.status !== 1;
                }),
                    [
                        x => { return x.url_short; },
                        x => { return x.ip_scanned; }
                    ]);
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

            week4renewals(){
                const r = _.filter(this.tlsCerts, x => {
                    return x && x.valid_to_days && x.valid_to_days <= 28;
                });
                const r2 = _.map(r, x => {
                    x.week4cat = util.week4grouper(x);
                    return x;
                });
                const grp = _.groupBy(r2, x => {
                    return x.week4cat;
                });
                return _.sortBy(grp, [x => {return x[0].valid_to_days; }]);
            },

            week4renewalsCounts(){
                const r = _.filter(this.tlsCerts, x => {
                    return x && x.valid_to_days && x.valid_to_days <= 28 && x.valid_to_days >= -28;
                });
                const ret = [0, 0, 0, 0, 0];
                _.forEach(r, x => {
                    ret[util.week4grouper(x)] += 1;
                });
                return ret;
            },

            tlsCertIssuers(){
                return util.certIssuersGen(this.tlsCerts);
            },

            allCertIssuers(){
                return util.certIssuersGen(this.certs);
            },

            certDomainDataset(){
                return [
                    util.certDomainsDataGen(this.tlsCerts),
                    util.certDomainsDataGen(this.certs),
                    util.certDomainsDataGen(this.tlsCerts, true),
                    util.certDomainsDataGen(this.certs, true)];
            },

            certDomainsTableData(){
                return _.toPairs(ReqD.flipGroups(this.certDomainDataset, {}));
            },
        },

        watch: {

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
            extendDateField: util.extendDateField,
            moment: util.moment,
            momentu: util.momentu,
            sortBy: util.sortBy,
            sortExpiry: util.sortExpiry,
            tblVal: util.tblVal,

            certIssuer: util.certIssuer,
            getCertHostPorts: util.getCertHostPorts,
            getCountCategoryLabelTbl(idx) { return util.getCountCategoryLabelTbl(idx) },

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
                const curTime = moment().valueOf() / 1000.0;
                for(const watch_id of Object.keys(this.results.watches)){
                    const watch = this.results.watches[watch_id];
                    this.extendDateField(watch, 'last_scan_at');
                    this.extendDateField(watch, 'created_at');
                    this.extendDateField(watch, 'updated_at');
                }

                const fqdnResolver = _.memoize(Psl.get);
                const wildcardRemover = _.memoize(Req.removeWildcard);
                for(const [certId, cert] of Object.entries(this.results.certificates)){
                    cert.valid_to_dayfmt = moment.utc(cert.valid_to_utc * 1000.0).format('YYYY-MM-DD');
                    cert.valid_to_days = Math.round(10 * (cert.valid_to_utc - curTime) / 3600.0 / 24.0) / 10;
                    cert.valid_from_days = Math.round(10 * (curTime - cert.valid_from_utc) / 3600.0 / 24.0) / 10;
                    cert.validity_sec = cert.valid_to_utc - cert.valid_from_utc;
                    cert.watch_hosts = [];
                    cert.watch_hostports = [];
                    cert.watch_urls = [];
                    cert.watch_hosts_ct = [];
                    cert.watch_urls_ct = [];
                    cert.alt_domains = _.sortedUniq(_.sortBy(_.map(_.castArray(cert.alt_names), x => {
                        return wildcardRemover(x);
                    })));
                    cert.alt_slds = _.sortedUniq(_.sortBy(_.map(_.castArray(cert.alt_domains), x => {
                        return fqdnResolver(x);  // too expensive now. 10 seconds for 150 certs. invoke later
                    })));

                    _.forEach(cert.tls_watches_ids, watch_id => {
                        if (watch_id in this.results.watches){
                            cert.watch_hostports.push(this.results.watches[watch_id].host_port);
                            cert.watch_hosts.push(this.results.watches[watch_id].scan_host);
                            cert.watch_urls.push(this.results.watches[watch_id].url);
                        }
                    });

                    _.forEach(
                        _.uniq(_.union(
                            cert.tls_watches_ids,
                            cert.crtsh_watches_ids)), watch_id =>
                        {
                            if (watch_id in this.results.watches) {
                                cert.watch_hosts_ct.push(this.results.watches[watch_id].scan_host);
                                cert.watch_urls_ct.push(this.results.watches[watch_id].url);
                            }
                        });

                    cert.watch_hostports = _.sortedUniq(cert.watch_hostports.sort());
                    cert.watch_hosts = _.sortedUniq(cert.watch_hosts.sort());
                    cert.watch_urls = _.sortedUniq(cert.watch_urls.sort());
                    cert.watch_hosts_ct = _.sortedUniq(cert.watch_hosts_ct.sort());
                    cert.watch_urls_ct = _.sortedUniq(cert.watch_urls_ct.sort());
                    cert.last_scan_at_utc = _.reduce(cert.tls_watches_ids, (acc, val) => {
                        if (!this.results.watches || !(val in this.results.watches)){
                            return acc;
                        }
                        const sc = this.results.watches[val].last_scan_at_utc;
                        return sc >= acc ? sc : acc;
                    }, null);

                    cert.planCss = {tbl: {
                        'success': cert.valid_to_days > 14 && cert.valid_to_days <= 28,
                        'warning': cert.valid_to_days > 7 && cert.valid_to_days <= 14,
                        'warning-hi': cert.valid_to_days > 0  && cert.valid_to_days <= 7,
                        'danger': cert.valid_to_days <= 0,
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

                Req.normalizeValue(this.results.certificates, 'issuerOrg', {
                    newField: 'issuerOrgNorm',
                    normalizer: Req.normalizeIssuer
                });

                for(const [whois_id, whois] of Object.entries(this.results.whois)){
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

                for(const [dns_id, dns] of Object.entries(this.results.dns)){
                    this.extendDateField(dns, 'last_scan_at');
                    dns.domain = this.results.watches && dns.watch_id in this.results.watches ?
                        this.results.watches[dns.watch_id].scan_host : undefined;
                }

                for(const [tls_id, tls] of Object.entries(this.results.tls)){
                    this.extendDateField(tls, 'last_scan_at');
                    this.extendDateField(tls, 'created_at');
                    this.extendDateField(tls, 'updated_at');
                    if (this.results.watches && tls.watch_id in this.results.watches){
                        tls.domain = this.results.watches[tls.watch_id].scan_host;
                        tls.url_short = this.results.watches[tls.watch_id].url_short;
                    }

                    tls.leaf_cert = tls.cert_id_leaf && tls.cert_id_leaf in this.results.certificates ?
                        this.results.certificates[tls.cert_id_leaf] : undefined;
                    if (tls.leaf_cert){
                        tls.host_cert = tls.leaf_cert;
                    } else if (tls.certs_ids && _.size(tls.certs_ids) === 1){
                        tls.host_cert = this.results.certificates[tls.certs_ids[0]];
                    }
                }

                this.$set(this.results, 'certificates', this.results.certificates);
                this.$forceUpdate();
                this.$emit('onProcessed');
                this.loadingState = 10;

                this.$nextTick(() => {
                    this.graphDataReady = true;
                    this.graphLibLoaded = true;
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
                if (!this.graphLibLoaded || !this.graphDataReady){
                    return;
                }

                this.graphsRendered = true;
                this.renderChartjs();
            },

            renderChartjs(){
                this.plannerGraph();
                this.certTypesGraph();
                this.week4renewGraph();
                this.certIssuersGraph();
                this.certDomainsGraph();
            },

            //
            // Subgraphs
            //

            plannerGraph(){
                const labels = ['Time', 'Let\'s Encrypt', 'Managed by CDN/ISP', 'Long validity'];
                const datasets = _.map([this.crtTlsMonth, this.crtAllMonth], x => {
                    return util.graphDataConv(_.concat([labels], x));
                });

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
                                stacked: true,
                                beginAtZero: true,
                                ticks: {
                                    callback: (value, index, values) => {
                                        return _.floor(value) == value ? value : null;
                                    }
                                }
                            }]
                        },
                        tooltips:{
                            mode: 'index'
                        },
                    }};

                const graphCrtTlsData = _.extend({data: datasets[0]}, _.cloneDeep(baseOptions));
                graphCrtTlsData.options.title = {
                    display: true,
                    text: 'Certificates on watched servers - excluding those hidden behind CDN/ISP proxies'
                };

                const graphCrtAllData = _.extend({data: datasets[1]}, _.cloneDeep(baseOptions));
                graphCrtAllData.options.title = {
                    display: true,
                    text: 'All issued certificates (CT)  - all valid certificates even when not detected on servers'
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
                                data: this.certTypesStatsAll,
                                backgroundColor: [util.chartColors[0], util.chartColors[1], util.chartColors[2]],
                                label: 'All issued certificates (CT)'
                            },
                            {
                                data: this.certTypesStats,
                                backgroundColor: [util.chartColors[0], util.chartColors[1], util.chartColors[2]],
                                label: 'Certificates on watched servers'
                            }],
                        labels: [
                            'Let\'s Encrypt',
                            'Managed by CDN/ISP',
                            'Long validity'
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
                if (!this.showImminentRenewals){
                    return;
                }

                // graph config
                const config = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: this.week4renewalsCounts,
                            backgroundColor: [
                                util.chartColors[12],
                                util.chartColors[3],
                                util.chartColors[1],
                                util.chartColors[0],
                                util.chartColors[2],
                            ],
                            label: 'Renewals in 4 weeks'
                        }],
                        labels: [
                            "expired",
                            "0-7 days",
                            "8-14 days",
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
                const tlsIssuerStats = ReqD.groupStats(this.tlsCertIssuers, 'count');
                const allIssuerStats = ReqD.groupStats(this.allCertIssuers, 'count');
                ReqD.mergeGroupStatsKeys([tlsIssuerStats, allIssuerStats]);
                ReqD.mergedGroupStatSort([tlsIssuerStats, allIssuerStats], ['1', '0'], ['desc', 'asc']);
                this.certIssuerTableData = _.sortBy(
                    ReqD.mergeGroupStatValues([tlsIssuerStats, allIssuerStats]),
                    x => {
                        return -1 * _.max(_.tail(x));
                    }
                );

                const tlsIssuerUnz = _.unzip(tlsIssuerStats);
                const allIssuerUnz = _.unzip(allIssuerStats);
                const graphCertTypes = {
                    type: 'horizontalBar',
                    data: {
                        datasets: [
                            {
                                data: tlsIssuerUnz[1],
                                backgroundColor: util.chartColors[0],
                                //backgroundColor: Req.takeMod(util.chartColors, tlsIssuerUnz[0].length),
                                label: 'Detected on servers'
                            },
                            {
                                data: allIssuerUnz[1],
                                backgroundColor: util.chartColors[2],
                                //backgroundColor: Req.takeMod(util.chartColors, allIssuerUnz[0].length),
                                label: 'From CT logs only'
                            }],
                        labels: allIssuerUnz[0]
                    },
                    options: {
                        scaleBeginAtZero: true,
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

            certDomainsGraph(){
                const dataGraphs = _.map(this.certDomainDataset, x=>{
                    return _.map(x, y => {
                        return [y.key, y.size];
                    });
                });

                ReqD.mergeGroupStatsKeys(dataGraphs);
                ReqD.mergedGroupStatSort(dataGraphs, ['0', '1'], ['asc', 'asc']);
                const unzipped = _.map(dataGraphs, _.unzip);

                // Normal domains
                const graphCertDomains = {
                    type: 'bar',
                    data: {
                        datasets: [
                            {
                                data: unzipped[0][1],
                                backgroundColor: util.chartColors[0],
                                //backgroundColor: Req.takeMod(util.chartColors, unzipped[0][1].length),
                                label: 'Watched servers'
                            },
                            {
                                data: unzipped[1][1],
                                backgroundColor: util.chartColors[2],
                                //backgroundColor: Req.takeMod(util.chartColors, unzipped[1][1].length),
                                label: 'All issued certificates (CT)'
                            }],
                        labels: _.map(unzipped[0][0], x => util.getCountCategoryLabel(x))
                    },
                    options: {
                        scaleBeginAtZero: true,
                        responsive: true,
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'All watched domains (server names)'
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                };

                // TLD domains
                const graphCertDomainsTld = {
                    type: 'bar',
                    data: {
                        datasets: [
                            {
                                data: unzipped[2][1],
                                backgroundColor: util.chartColors[0],
                                //backgroundColor: Req.takeMod(util.chartColors, unzipped[2][1].length),
                                label: 'Watched servers'
                            },
                            {
                                data: unzipped[3][1],
                                backgroundColor: util.chartColors[2],
                                //backgroundColor: Req.takeMod(util.chartColors, unzipped[3][1].length),
                                label: 'All issued certificates (CT)'
                            }],
                        labels: _.map(unzipped[2][0], x => util.getCountCategoryLabel(x))
                    },
                    options: {
                        scaleBeginAtZero: true,
                        responsive: true,
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Registered domains (SLD)'
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                };

                setTimeout(() => {
                    new Chart(document.getElementById("pie_cert_domains"), graphCertDomains);
                    // new Chart(document.getElementById("pie_cert_domains_tld"), graphCertDomainsTld);
                }, 1000);

            },

            //
            // Common graph data gen
            //

            certTypes(certSet){
                // certificate type aggregation
                const certTypes = [0, 0, 0];  // LE, Cloudflare, Public / other

                for(const [crtIdx, ccrt] of Object.entries(certSet)){
                    if (ccrt.is_le){
                        certTypes[0] += 1
                    } else if (ccrt.is_cloudflare || ccrt.id in this.cdnCerts){
                        certTypes[1] += 1
                    } else {
                        certTypes[2] += 1
                    }
                }
                return certTypes;
            },

            monthDataGen(certSet){
                // cert per months, LE, Cloudflare, Others
                const newSet = util.extrapolatePlannerCerts(certSet);
                const grp = _.groupBy(newSet, x => {
                    return moment.utc(x.valid_to_utc * 1000.0).format('YYYY-MM');
                });

                const fillGap = (ret, lastMoment, toMoment) => {
                    if (_.isUndefined(lastMoment) || lastMoment >= toMoment){
                        return;
                    }

                    const terminal = toMoment.format('MM/YY');
                    const i = moment.utc(lastMoment).add(1, 'month');
                    for(i; i.format('MM/YY') !== terminal && i < toMoment; i.add(1, 'month')){
                        ret.push([ i.format('MM/YY'), 0, 0, 0]);
                    }
                };

                const sorted = _.sortBy(grp, [x => {return x[0].valid_to_utc; }]);
                const ret = [];
                let lastGrp = moment().utc().subtract(1, 'month');
                for(const [idx, grp] of Object.entries(sorted)){
                    const crt = grp[0];
                    const curMoment = moment.utc(crt.valid_to_utc * 1000.0);
                    const label = curMoment.format('MM/YY');

                    fillGap(ret, lastGrp, curMoment);
                    const certTypesStat = this.certTypes(grp);
                    const curEntry = [label, certTypesStat[0], certTypesStat[1], certTypesStat[2]];
                    ret.push(curEntry);
                    lastGrp = curMoment;
                }

                fillGap(ret, lastGrp, moment().utc().add(1, 'year').add(1, 'month'));
                return ret;
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

    .info-box-label {
        line-height: 80px;
        padding-left: 50px;
        font-size: 20px;
        font-weight: 400;
        color: #444;
    }

</style>

