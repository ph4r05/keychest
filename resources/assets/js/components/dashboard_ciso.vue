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
                <span>Processing data...</span>
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
                <!-- HEADLINE: external certificate compliance -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box"
                         v-bind:class="{'bg-green': len(certs) > 1, 'bg-red': len(certs) < 1}" >
                        <div class="inner">
                            <h3>100% <span style='font-size: smaller;'>of {{len(certs)}}</span></h3>

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
                         v-bind:class="{'bg-green': tlsNonCompliance.length < 1,
                          'bg-yellow': tlsNonCompliance.length*10 < len(tls),
                          'bg-red': tlsNonCompliance.length*10 >= len(tls)}" >
                        <div class="inner">
                            <h3>{{ Math.round(100*(len(tls)-tlsNonCompliance.length)/len(tls)) }}% <span style='font-size: smaller;'>of {{len(tls)}}</span></h3>
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
                            'bg-red': 1>0}" >
                        <div class="inner">
                            <h3>{{ Math.round(100*(len(tls)-tlsErrors.length)/len(tls)) }}% /
                                {{ Math.round(100*(len(certs)-len(tlsCerts))/len(certs)) }}%</h3>
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
                        <span class="progress-description">{{ Math.round(100*(len(tls)-tlsErrors.length)/len(tls)) *
                                Math.round((len(certs)-len(tlsCerts))/len(certs)) }}% compliance
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
                        <p>The table shows the compliance summary ofer the last 12 months. TODO
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
                                <td>{{tlsNonCompliance.length}}</td>
                                </tr>

                                <tr>
                                <td>Incorrect certificates</td>
                                <td>{{len(tlsInvalidHostname)}}</td>
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


            <!-- Section heading -->
            <div class="row" v-if="
                    len(tlsInvalidTrust) > 0 ||
                    len(tlsInvalidHostname) > 0
                ">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-exclamation-circle"></i></span>
                    <div class="info-box-content info-box-label">
                        Non-compliance details
                    </div>
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
                                    <td>{{ utcTimeLocaleString(tls.created_at_utc) }}
                                        ({{ momentu(tls.created_at_utc * 1000.0).fromNow() }})</td>
                                    <td>{{ utcTimeLocaleString(tls.last_scan_at_utc) }}</td>
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
                                    <td>{{ utcTimeLocaleString(tls.created_at_utc) }}
                                        ({{ momentu(tls.created_at_utc * 1000.0).fromNow() }})</td>
                                    <td>{{ utcTimeLocaleString(tls.last_scan_at_utc) }}</td>
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

            <!-- Certificate types -->
            <div class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-primary" :headerCollapse="true">
                        <template slot="title">Certificate overview</template>
                        <div class="form-group">
                            <p>
                                Certificates in your inventory can be managed by third-party (CDN or ISP). You are
                                responsible for renewing certificate issued by Let&#39;s Encrypt (short validity
                                certificates) and by other authorities (long validity certificates).
                            </p>
                            <canvas id="pie_cert_types_ciso" style="width: 100%; height: 350px;"></canvas>
                        </div>
                    </sbox>
                </div>
            </div>

            <!-- Certificate issuers -->
            <div class="row" v-if="certIssuerTableData">
                <div class="xcol-md-12">
                    <sbox cssBox="box-primary" :headerCollapse="true">
                        <template slot="title">Number of certificates per issuer</template>
                        <div class="table-responsive table-xfull" style="margin-bottom: 10px">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Provider</th>
                                <th>Watched servers</th>
                                <th>All issued certificates (CT)</th>
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
                            <canvas id="pie_cert_issuers_ciso" style="width: 100%; height: 500px;"></canvas>
                        </div>
                    </sbox>
                </div>
            </div>
            <div class="row" v-else="">
                <div class="xcol-md-12">
                    <sbox cssBox="box-primary" :headerCollapse="true">
                        <template slot="title">Number of certificates per issuer</template>
                        <p>No data is available</p>
                    </sbox>
                </div>
            </div>



            <!-- Certificate domains -->
            <div class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-primary" :headerCollapse="true">
                        <template slot="title">Number of server names in SAN certificates</template>
                        <p>Certificates can be used for multiple servers (domain names).
                            The table shows how many servers can use a certain certificate.
                            This information has an impact on the cost of certificats, if there issuance
                            is not free.</p>

                        <div class="table-responsive table-xfull" style="margin-bottom: 10px">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th rowspan="2">No of server names<br/> in certificates</th>
                                    <th colspan="3">Certificates on watched servers</th>
                                    <th colspan="3">All issued certificates (CT)</th>
                                    <!--<th colspan="3">Watched SLDs</th>-->
                                    <!--<th colspan="3">SLDs in global logs</th> -->
                                </tr>
                                <tr>
                                    <th>Let&#39;s Encrypt</th>
                                    <th>Other certificates</th>
                                    <th><i>Number of all issuers</i></th>

                                    <th>Let&#39;s Encrypt</th>
                                    <th>Other certificates</th>
                                    <th><i>Number of all issuers</i></th>

                                    <!--<th>Certs</th>-->
                                    <!--<th>Issuers</th>-->
                                    <!--<th>LE</th>-->

                                    <!--<th>Certs</th>-->
                                    <!--<th>Issuers</th>-->
                                    <!--<th>LE</th>-->
                                </tr>
                                </thead>

                                <tbody>
                                <tr v-for="group in certDomainsTableData">
                                    <td>{{ getCountCategoryLabelTbl(group[0]) }}</td>

                                    <td>{{ tblVal(group[1][0].leCnt) }}</td>
                                    <td v-if="isNaN(tblVal(group[1][0].leCnt))">{{ tblVal(group[1][0].size) }}</td>
                                    <td v-else="">{{ tblVal(group[1][0].size) - tblVal(group[1][0].leCnt) }}</td>
                                    <td><i>{{ tblVal(group[1][0].distIssuers) }}</i></td>

                                    <td>{{ tblVal(group[1][1].leCnt) }}</td>
                                    <td v-if="isNaN(tblVal(group[1][1].leCnt))">{{ tblVal(group[1][1].size) }}</td>
                                    <td v-else="">{{ tblVal(group[1][1].size) - tblVal(group[1][1].leCnt) }}</td>
                                    <td><i>{{ tblVal(group[1][1].distIssuers) }}</i></td>

                                    <!--<td>{{ tblVal(group[1][2].size) }}</td>-->
                                    <!--<td>{{ tblVal(group[1][2].distIssuers) }}</td>-->
                                    <!--<td>{{ tblVal(group[1][2].leCnt) }}</td>-->

                                    <!--<td>{{ tblVal(group[1][3].size) }}</td>-->
                                    <!--<td>{{ tblVal(group[1][3].distIssuers) }}</td>-->
                                    <!--<td>{{ tblVal(group[1][3].leCnt) }}</td>-->
                                </tr>
                                </tbody>
                            </table>
                        </div>


                        <div class="col-md-12">
                            <canvas id="pie_cert_domains_ciso" style="height: 400px;"></canvas>
                        </div>
                        <!--<div class="col-md-6">-->
                            <!--<canvas id="pie_cert_domains_tld" style=" height: 400px;"></canvas>-->
                        <!--</div>-->
                    </sbox>
                </div>
            </div>

            <!-- All Certificate list -->
            <a name="allCerts"></a>
            <div class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-primary" :collapsed="true" :headerCollapse="true">
                        <template slot="title">All certificates of your servers</template>
                        <div class="form-group">
                            <p style="color:red">Expiration will be replaced with compliance info</p>
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
                                <tr v-for="cert in sortExpiry(certs)" v-if="cert.planCss">
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
    import util from './dashboard/util';
    import charts from './dashboard/charts';

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

                graphsRendered: false,
                graphDataReady: false,

                certIssuerTableData: null,
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
                return util.imminentRenewalCerts(this.tlsCerts);
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
                return util.tlsErrors(this.tls);
            },

            tlsNonCompliance(){
                return _.filter(this.tls, x => {
                        return x && x.status === 1 && x.valid_hostname;
                    });
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
                return util.week4renewals(this.tlsCerts);
            },

            week4renewalsCounts(){
                return util.week4renewalsCounts(this.tlsCerts);
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

            take: util.take,
            len: util.len,
            utcTimeLocaleString: util.utcTimeLocaleString,
            utcTimeLocaleStringUs: Req.del(util.utcTimeLocaleStringUs, util),
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
                const [graphCrtTlsData, graphCrtAllData] = charts.plannerConfig(this.crtTlsMonth, this.crtAllMonth);
                new Chart(document.getElementById("columnchart_certificates_js_ciso"), graphCrtTlsData);
                new Chart(document.getElementById("columnchart_certificates_all_js_ciso"), graphCrtAllData);
            },

            certTypesGraph(){
                const graphCertTypes = charts.certTypesConfig(this.certTypesStatsAll, this.certTypesStats);
                new Chart(document.getElementById("pie_cert_types_ciso"), graphCertTypes);
            },

            week4renewGraph(){
                if (!this.showImminentRenewals){
                    return;
                }

                const config = charts.week4renewConfig(this.week4renewalsCounts);
                setTimeout(() => {
                    new Chart(document.getElementById("imminent_renewals_js_ciso"), config);
                }, 1000);
            },

            certIssuersGraph(){
                const tlsIssuerStats = ReqD.groupStats(this.tlsCertIssuers, 'count');
                const allIssuerStats = ReqD.groupStats(this.allCertIssuers, 'count');
                ReqD.mergeGroupStatsKeys([tlsIssuerStats, allIssuerStats]);
                ReqD.mergedGroupStatSort([tlsIssuerStats, allIssuerStats], ['1', '0'], ['desc', 'asc']);
                this.certIssuerTableData = _.sortBy(
                    ReqD.mergeGroupStatValues([tlsIssuerStats, allIssuerStats]),
                    util.invMaxTail);

                const tlsIssuerUnz = _.unzip(tlsIssuerStats);
                const allIssuerUnz = _.unzip(allIssuerStats);
                const graphCertTypes = charts.certIssuerConfig(allIssuerUnz, tlsIssuerUnz);

                setTimeout(() => {
                    new Chart(document.getElementById("pie_cert_issuers_ciso"), graphCertTypes);
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
                const graphCertDomains = charts.certDomainsConfig(unzipped, 'All watched domains (server names)');

                // const unzippedTld = [unzipped[2], unzipped[3]];
                // const graphCertDomainsTld = charts.certDomainsConfig(unzippedTld, 'Registered domains (SLD)');

                setTimeout(() => {
                    new Chart(document.getElementById("pie_cert_domains_ciso"), graphCertDomains);
                    // new Chart(document.getElementById("pie_cert_domains_tld"), graphCertDomainsTld);
                }, 1000);

            },

            //
            // Common graph data gen
            //

            certTypes(certSet){
                return util.certTypes(certSet, this.cdnCerts);
            },

            monthDataGen(certSet){
                return util.monthDataGen(certSet, {'cdnCerts': this.cdnCerts});
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

