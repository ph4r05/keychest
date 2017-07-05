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

                <!-- HEADLINE: certificates expire now -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-red">
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
                    <div class="small-box bg-yellow">
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
                        <a v-else-if="(numExpiresSoon) == 0"
                           target="_blank"
                           href="https://www.tripadvisor.co.uk/Search?geo=&latitude=&longitude=&searchNearby=&redirect=&startTime=&uiOrigin=&q=holiday"
                           class="small-box-footer">Take a holiday <i class="fa fa-arrow-circle-right"></i></a>
                        <a v-else=""
                           href="#"
                           class="small-box-footer">A break after this week</a>
                    </div>
                </div>
                <!-- ./col -->

                <!-- HEADLINE: no of servers -->
                <div class="col-lg-3 col-xs-6">
                    <!-- small box -->
                    <div class="small-box bg-green" v-if="1==1">
                        <div class="inner">
                            <h3>{{ numWatches }}</h3>
                            <p>Watched servers</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-server"></i> <!--fa-sitemap-->
                        </div>
                        <a href="/home/servers" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>

                    <div class="small-box bg-red" v-else="">
                        <div class="inner">
                            <h3>X of {{ numWatches }}</h3>
                            <p>Watched servers DOWN</p>
                        </div>

                        <div class="icon">
                            <i class="fa fa-server"></i> <!--fa-sitemap-->
                        </div>
                        <a href="/home/servers" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
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

            </div>

            <!-- Section heading -->
            <div class="row">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-tachometer"></i></span>
                    <div class="info-box-content info-box-label">
                        Key Management Report - {{ Date() }}
                        <!-- TODO: fix: Date(tls.created_at_utc * 1000.0 - (new Date().getTimezoneOffset())*60) -->
                    </div>
                </div>
            </div>

            <!-- Monthly planner -->
            <div class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-success">
                        <template slot="title">Yearly renewal calendar</template>
                            <div class="form-group">
                                <canvas id="columnchart_certificates_js" style="width:100%; height: 350px"></canvas>
                            </div>

                            <div class="form-group">
                                <canvas id="columnchart_certificates_all_js" style="width:100%; height: 350px"></canvas>
                            </div>
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
            <div v-if="dnsFailedLookups.length > 0" class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-danger">
                        <template slot="title">DNS configuration issues</template>
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
                    <sbox cssBox="box-danger">
                        <template slot="title">Unreachable servers</template>

                        <p>We failed to connect to one or more servers using TLS protocol.</p>
                        <div class="table-responsive table-xfull">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>Server name</th>
                                    <th>Cause</th>
                                    <th>Time of detection</th>
                                    <th>Last failure</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="tls in tlsErrors" class="danger">
                                    <td>{{ tls.urlShort }}</td>
                                    <td>
                                        <span v-if="tls.err_code == 1">TLS handshake error</span>
                                        <span v-else-if="tls.err_code == 2">No server detected</span>
                                        <span v-else-if="tls.err_code == 3">Timeout</span>
                                        <span v-else-if="tls.err_code == 4">Domain lookup error</span>
                                        <span v-else="">TLS not present</span>
                                    </td>
                                    <td>{{ new Date(tls.created_at_utc * 1000.0).toLocaleString() }}
                                         ({{ moment(tls.created_at_utc * 1000.0).fromNow() }})</td>
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
                    <sbox cssBox="box-danger">
                        <template slot="title">Servers with configuration errors</template>
                        <p>We detected security or configuration problems at following servers</p>
                        <div class="table-responsive table-xfull">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>Server name</th>
                                    <th>Cause</th>
                                    <th>Time of detection</th>
                                    <th>Last failure</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="tls in sortBy(tlsInvalidTrust, 'created_at_utc')" class="danger">
                                    <td>{{ tls.urlShort }}</td>
                                    <td>
                                        <ul class="domain-list">
                                            <li v-if="tls.host_cert && tls.host_cert.is_self_signed">Self-signed certificate</li>
                                            <li v-if="tls.host_cert && tls.host_cert.is_ca">CA certificate</li>
                                            <li v-if="tls.host_cert && len(tls.certs_ids) > 1">Validation failed</li>
                                            <li v-else-if="len(tls.certs_ids) === 1">Validation failed - incomplete chain</li>
                                            <li v-else-if="len(tls.certs_ids) === 0">No certificate</li>
                                            <li v-else-if="tls.host_cert">Untrusted certificate</li>
                                            <li v-else="">No host certificate</li>
                                        </ul>
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

            <!-- TLS hostname errors -->
            <div v-if="len(tlsInvalidHostname) > 0" class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-danger">
                        <template slot="title">Unused, default, or incorrect certificates</template>
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
                                    <td>{{ tls.urlShort }}</td>
                                    <td>
                                        <ul class="coma-list" v-if="tls.host_cert">
                                            <li v-for="domain in take(tls.host_cert.alt_domains, 10)">{{ domain }}</li>
                                        </ul>
                                        <span v-else="">No domains found</span>
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

            <!-- TLS expired certificates -->
            <div v-if="len(expiredCertificates) > 0" class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-danger">
                        <template slot="title">Servers with expired certificates</template>
                        <p>Clients can't connect to following servers due to expired certificates.</p>
                        <div class="table-responsive table-xfull">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>Server name</th>
                                    <th>Certificate issuers</th>
                                    <th>Expiration date</th>
                                    <th>Last failure</th>
                                    <!--                                    <th>ID</th> -->
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="cert in sortBy(expiredCertificates, 'expires_at_utc')" class="danger">
                                    <td>
                                        <ul class="domain-list">
                                            <li v-for="domain in cert.watch_hosts">
                                                {{ domain }}
                                            </li>
                                        </ul>
                                    </td>
                                    <td>{{ cert.issuerOrgNorm }}</td>
                                    <td>{{ new Date(cert.valid_to_utc * 1000.0).toLocaleString() }}
                                        ({{ moment(cert.valid_to_utc * 1000.0).fromNow() }})</td>
                                    <td>{{ new Date(cert.last_scan_at_utc * 1000.0).toLocaleString() }}
                                        ({{ moment(cert.last_scan_at_utc * 1000.0).fromNow() }})</td>
                                    <!--                                    <td>{{ cert.id }}</td>-->
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </sbox>
                </div>
            </div>


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
            <div v-if="showImminentRenewals" class="row">
                <div class="xcol-md-12">
                <sbox cssBox="box-success">
                    <template slot="title">Renewals due in next 28 days</template>
                    <div class="col-md-6">
                        <div class="table-responsive table-xfull">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th colspan="2">Prevent downtime, renew by</th>
                                    <th>Server names</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="grp in imminentRenewalCerts">
                                    <td colspan="2" v-bind:class="grp[0].planCss.tbl" v-if="moment(grp[0].valid_to_utc * 1000.0)<new Date()">
                                        SERVER DOWN since {{ moment(grp[0].valid_to_utc * 1000.0).fromNow() }} </td>
                                    <td colspan="2" v-bind:class="grp[0].planCss.tbl" v-else="">
                                        {{ new Date(grp[0].valid_to_utc * 1000.0).toLocaleDateString() }}
                                        ({{ moment(grp[0].valid_to_utc * 1000.0).fromNow() }}) </td>
                                    <td v-bind:class="grp[0].planCss.tbl">
                                        <ul class="coma-list" v-if="len(getCertHostPorts(grp)) > 0">
                                            <li v-for="domain in getCertHostPorts(grp)">{{ domain }}</li>
                                        </ul>
                                        <span v-else="">No domains found</span>
                                    </td>
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
                    <sbox cssBox="box-success">
                        <template slot="title">Domain name expiration dates</template>
                        <p>The following domain names' registration expires within 90 days.</p>
                        <div class="table-responsive table-xfull">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Domain name</th>
                                <th>You have to renew</th>
                                <th>Expiration date</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="cur_whois in sortBy(whois, 'expires_at_utc')" v-if="cur_whois.expires_at_days <= 90">
                                <td v-bind:class="cur_whois.planCss.tbl">
                                    {{ cur_whois.domain }} </td>
                                <td v-bind:class="cur_whois.planCss.tbl">
                                    {{ moment(cur_whois.expires_at_utc * 1000.0).fromNow() }} </td>
                                <td v-bind:class="cur_whois.planCss.tbl">
                                    {{ new Date(cur_whois.expires_at_utc * 1000.0).toLocaleDateString() }}</td>
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
                    <sbox cssBox="box-warning">
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
                    <sbox cssBox="box-success">
                        <template slot="title">Certificate overview</template>
                        <div class="form-group">
                            <p>
                                Certificates in your inventory can be managed by third-party (CDN or ISP). You are
                                responsible for renewing certificate issued by Let’s Encrypt (short validity
                                certificates) and by other authorities (long validity certificates).
                            </p>
                            <canvas id="pie_cert_types" style="width: 100%; height: 350px;"></canvas>
                        </div>
                    </sbox>
                </div>
            </div>

            <!-- Certificate issuers -->
            <div class="row" v-if="certIssuerTableData">
                <div class="xcol-md-12">
                    <sbox cssBox="box-success">
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
                            <canvas id="pie_cert_issuers" style="width: 100%; height: 500px;"></canvas>
                        </div>
                    </sbox>
                </div>
            </div>


            <!-- Certificate domains -->
            <div class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-primary">
                        <template slot="title">Number of server names in SAN certificates</template>
                        <p>Certificates can be used for multiple servers (domain names).
                            The table shows how many certificates contain a certain number of server names.
                            SLD – second-level domain names.</p>

                        <div class="table-responsive table-xfull" style="margin-bottom: 10px">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th rowspan="2">Servers</th>
                                    <th colspan="3">Watched servers</th>
                                    <th colspan="3">Global logs</th>
                                    <th colspan="3">Watched SLDs</th>
                                    <th colspan="3">SLDs in global logs</th>
                                </tr>
                                <tr>
                                    <th>Certs</th>
                                    <th>Issuers</th>
                                    <th>LE</th>

                                    <th>Certs</th>
                                    <th>Issuers</th>
                                    <th>LE</th>

                                    <th>Certs</th>
                                    <th>Issuers</th>
                                    <th>LE</th>

                                    <th>Certs</th>
                                    <th>Issuers</th>
                                    <th>LE</th>
                                </tr>
                                </thead>

                                <tbody>
                                <tr v-for="group in certDomainsTableData">
                                    <td>{{ getCountCategoryLabelTbl(group[0]) }}</td>

                                    <td>{{ tblVal(group[1][0].size) }}</td>
                                    <td>{{ tblVal(group[1][0].distIssuers) }}</td>
                                    <td>{{ tblVal(group[1][0].leCnt) }}</td>

                                    <td>{{ tblVal(group[1][1].size) }}</td>
                                    <td>{{ tblVal(group[1][1].distIssuers) }}</td>
                                    <td>{{ tblVal(group[1][1].leCnt) }}</td>

                                    <td>{{ tblVal(group[1][2].size) }}</td>
                                    <td>{{ tblVal(group[1][2].distIssuers) }}</td>
                                    <td>{{ tblVal(group[1][2].leCnt) }}</td>

                                    <td>{{ tblVal(group[1][3].size) }}</td>
                                    <td>{{ tblVal(group[1][3].distIssuers) }}</td>
                                    <td>{{ tblVal(group[1][3].leCnt) }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>


                        <div class="col-md-6">
                            <canvas id="pie_cert_domains" style="height: 400px;"></canvas>
                        </div>
                        <div class="col-md-6">
                            <canvas id="pie_cert_domains_tld" style=" height: 400px;"></canvas>
                        </div>
                    </sbox>
                </div>
            </div>

            <!-- Certificate list -->
            <a name="certs"></a>
            <div class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-primary">
                        <template slot="title">Certificate list</template>
                        <p>All certificates on watched servers ({{ len(tlsCerts) }})</p>
                        <div class="table-responsive table-xfull">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Expiration</th>
                                    <th>Relative</th>
                                    <th>Server names</th>
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
            <a name="allCerts"></a>
            <div class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-primary">
                        <template slot="title">Complete Certificate list</template>
                        <div class="form-group">
                            <p>All certificates in Certificate Transparency public logs ({{ len(certs) }})</p>
                            <input type="checkbox" id="chk-include-expired">
                            <label for="chk-include-expired">Include expired certificates</label>
                        </div>
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

                graphLibLoaded: false,
                graphsRendered: false,
                graphDataReady: false,

                certIssuerTableData: null,
                includeExpired: false,

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
            this.$nextTick(function () {
                this.hookup();
            })
        },

        computed: {
            hasAccount(){
                return !this.Laravel.authGuest;
            },

            tlsCertsIdsMap(){
                if (!this.results || !this.results.tls_cert_map){
                    return {};
                }

                return this.listToSet(_.uniq(_.values(this.results.tls_cert_map)));
            },

            tlsCerts(){
                // return _.filter(this.certs, o => { return o.found_tls_scan; });
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
                    return cur.valid_to_days <= 28;
                }));
            },

            numExpiresNow(){
                return Number(_.sumBy(this.tlsCerts, cur => {
                    return cur.valid_to_days <= 8;
                }));
            },

            numWatches(){
                return this.results ? _.size(this.results.watches) : 0;
            },

            showImminentRenewals(){
                return _.reduce(this.tlsCerts, (acc, cur) => {
                    return acc + (cur.valid_to_days <= 28);
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
                const imm = _.filter(this.tlsCerts, x => { return x.valid_to_days <= 28 });
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
                return _.filter(this.tls, x => {
                    return x && x.status !== 1;
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

            tlsCertIssuers(){
                return this.certIssuersGen(this.tlsCerts);
            },

            allCertIssuers(){
                return this.certIssuersGen(this.certs);
            },

            certDomainDataset(){
                return [
                    this.certDomainsDataGen(this.tlsCerts),
                    this.certDomainsDataGen(this.certs),
                    this.certDomainsDataGen(this.tlsCerts, true),
                    this.certDomainsDataGen(this.certs, true)];
            },

            certDomainsTableData(){
                return _.toPairs(this.flipGroups(this.certDomainDataset, {}));
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

            tblVal(x){
                return x ? x : '-';
            },

            getCountCategoryLabelTbl(idx){
                if (idx >= this.countCategories.length){
                    return _.last(this.countCategories) + '+';
                } else if (idx == 0) {
                    return this.countCategories[0]
                }

                return this.countCategories[idx-1] + '-' + this.countCategories[idx];
            },

            getCertHostPorts(certSet){
                return _.sortedUniq(_.sortBy(_.reduce(_.castArray(certSet), (acc, x) => {
                    return _.concat(acc, x.watch_hostports);
                }, [])));
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
                return Req.certIssuer(cert);
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
                    this.extendDateField(watch, 'last_scan_at');
                    this.extendDateField(watch, 'created_at');
                    this.extendDateField(watch, 'updated_at');

                    const strPort = parseInt(watch.scan_port) || 443;
                    watch.url = Req.buildUrl(watch.scan_scheme, watch.scan_host, strPort);
                    watch.urlShort = Req.buildUrl(watch.scan_scheme, watch.scan_host, strPort === 443 ? undefined : strPort);
                    watch.hostport = watch.scan_host + (strPort === 443 ? '' : ':' + strPort);
                }

                const fqdnResolver = _.memoize(Psl.get);
                const wildcardRemover = _.memoize(Req.removeWildcard);
                for(const certId in this.results.certificates){
                    const cert = this.results.certificates[certId];
                    cert.valid_to_dayfmt = moment(cert.valid_to_utc * 1000.0).format('YYYY-MM-DD');
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

                    _.forEach(cert.tls_watches, watch_id => {
                        if (watch_id in this.results.watches){
                            cert.watch_hostports.push(this.results.watches[watch_id].hostport);
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

                    cert.watch_hostports = _.uniq(cert.watch_hostports.sort());
                    cert.watch_hosts = _.uniq(cert.watch_hosts.sort());
                    cert.watch_urls = _.uniq(cert.watch_urls.sort());
                    cert.watch_hosts_ct = _.uniq(cert.watch_hosts_ct.sort());
                    cert.watch_urls_ct = _.uniq(cert.watch_urls_ct.sort());
                    cert.last_scan_at_utc = _.reduce(cert.tls_watches, (acc, val) => {
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

                this.$nextTick(function () {
                    this.graphDataReady = true;
                    this.graphLibLoaded = true;
                    this.renderCharts();
                    this.postLoad();
                    const processTime = moment().diff(this.dataProcessStart);
                    console.log('Processing finished in ' + processTime + ' ms');
                });
            },

            postLoad(){
                const chkInclude = $('#chk-include-expired');
                chkInclude.bootstrapSwitch();
                chkInclude.on('switchChange.bootstrapSwitch', (evt, state) => {
                    if (evt.type === 'switchChange'){
                        this.includeExpired = state;
                    }
                });
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
                    return this.graphDataConv(_.concat([labels], x));
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
                    text: 'Certificates on watched servers'
                };

                const graphCrtAllData = _.extend({data: datasets[1]}, _.cloneDeep(baseOptions));
                graphCrtAllData.options.title = {
                    display: true,
                    text: 'All issued certificates (CT)'
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
                                backgroundColor: [this.chartColors[0], this.chartColors[1], this.chartColors[2]],
                                label: 'All issued certificates (CT)'
                            },
                            {
                                data: this.certTypesStats,
                                backgroundColor: [this.chartColors[0], this.chartColors[1], this.chartColors[2]],
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
                    type: 'horizontalBar',
                    data: {
                        datasets: [
                            {
                                data: tlsIssuerUnz[1],
                                backgroundColor: this.chartColors[0],
                                //backgroundColor: this.takeMod(this.chartColors, tlsIssuerUnz[0].length),
                                label: 'Detected on servers'
                            },
                            {
                                data: allIssuerUnz[1],
                                backgroundColor: this.chartColors[2],
                                //backgroundColor: this.takeMod(this.chartColors, allIssuerUnz[0].length),
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

                this.mergeGroupStatsKeys(dataGraphs);
                this.mergedGroupStatSort(dataGraphs, ['0', '1'], ['asc', 'asc']);
                const unzipped = _.map(dataGraphs, _.unzip);

                // Normal domains
                const graphCertDomains = {
                    type: 'bar',
                    data: {
                        datasets: [
                            {
                                data: unzipped[0][1],
                                backgroundColor: this.chartColors[0],
                                //backgroundColor: this.takeMod(this.chartColors, unzipped[0][1].length),
                                label: 'Watched servers'
                            },
                            {
                                data: unzipped[1][1],
                                backgroundColor: this.chartColors[2],
                                //backgroundColor: this.takeMod(this.chartColors, unzipped[1][1].length),
                                label: 'All issued certificates (CT)'
                            }],
                        labels: _.map(unzipped[0][0], this.getCountCategoryLabel)
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
                                backgroundColor: this.chartColors[0],
                                //backgroundColor: this.takeMod(this.chartColors, unzipped[2][1].length),
                                label: 'Watched servers'
                            },
                            {
                                data: unzipped[3][1],
                                backgroundColor: this.chartColors[2],
                                //backgroundColor: this.takeMod(this.chartColors, unzipped[3][1].length),
                                label: 'All issued certificates (CT)'
                            }],
                        labels: _.map(unzipped[2][0], this.getCountCategoryLabel)
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
                    new Chart(document.getElementById("pie_cert_domains_tld"), graphCertDomainsTld);
                }, 1000);

            },

            //
            // Common graph data gen
            //

            getCountCategoryLabel(idx){
                if (idx >= this.countCategories.length){
                    return _.last(this.countCategories) + '+';
                }
                return this.countCategories[idx];
            },

            getCountCategory(count){
                let ret = -1;
                const ln = this.countCategories.length;
                for(let idx=0; idx < ln; idx++){
                    if (count > this.countCategories[idx]){
                        ret = idx;
                    } else {
                        break;
                    }
                }

                return ret+1;
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

            extrapolatePlannerCerts(certSet){
                // Adds certificates to the planner multiple times for planner if validity len < 12M
                const valid12m = 3600 * 24 * 365;
                const filtered = _.filter(certSet, x => {
                    return x.validity_sec < valid12m;
                });

                if (_.size(filtered) === 0){
                    return certSet;
                }

                // Has to clone, we dont want to add extrapolated certificates to other graphs
                const newSet = _.clone(_.castArray(certSet));
                const threshold = moment().add(1, 'year').unix();

                // Add each cert
                _.forEach(filtered, cert => {
                    let lastCert = cert;
                    while(lastCert.valid_to_utc + lastCert.validity_sec < threshold){
                        // create just a lightweight shim, later for full clone do: _.cloneDeep(lastCert);
                        const cloned = { is_clone: true };
                        cloned.is_le = lastCert.is_le;
                        cloned.is_cloudflare = lastCert.is_cloudflare;
                        cloned.validity_sec = lastCert.validity_sec;
                        cloned.valid_to_utc = lastCert.valid_to_utc + lastCert.validity_sec;
                        cloned.valid_from_utc = lastCert.valid_to_utc;
                        newSet.push(cloned);
                        lastCert = cloned;
                    }
                });

                return newSet;
            },

            monthDataGen(certSet){
                // cert per months, LE, Cloudflare, Others
                const newSet = this.extrapolatePlannerCerts(certSet);
                const grp = _.groupBy(newSet, x => {
                    return moment(x.valid_to_utc * 1000.0).format('YYYY-MM');
                });

                const fillGap = (ret, lastMoment, toMoment) => {
                    if (_.isUndefined(lastMoment) || lastMoment >= toMoment){
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
                let lastGrp = moment().subtract(1, 'month');
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

            certDomainsDataGen(certSet, tld){
                const grouped = tld ? this.groupTldDomainsCount(certSet) : this.groupDomainsCount(certSet);
                return _.mapValues(grouped, (cur, key) => {
                    const grp = _.castArray(cur);
                    return {
                        key: key,
                        lbl: this.getCountCategoryLabel(key),
                        size: _.size(grp),
                        distIssuers: _.size(_.groupBy(grp, x => { return x.issuerOrgNorm; })),
                        leCnt: _.size(_.filter(grp, x => { return x.is_le; })),
                        issuerHist: _.countBy(grp, x => { return x.issuerOrgNorm; }),
                        certs: grp
                    };
                });
            },

            certIssuersGen(certSet){
                const grp = _.groupBy(certSet, x => {
                    return x.issuerOrgNorm;
                });
                return grp; //return _.sortBy(grp, [x => {return x[0].issuerOrg; }]);
            },

            groupDomainsCount(certSet){
                return _.groupBy(certSet, x=> {
                    return this.getCountCategory(_.size(x.alt_domains));
                });
            },

            groupTldDomainsCount(certSet){
                return _.groupBy(certSet, x=> {
                    return this.getCountCategory(_.size(x.alt_slds));
                });
            },

            groupStats(grouped, sort){
                // processes groupBy result and returns [[key1, size1], [key2, size2], ...]
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
                return this.mergeGroupKeys(groups, 0);
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

            mergeGroupKeys(groups, missing){
                // [g1 => [[l1,obj1], [l2,obj2]], ...]
                // after this function all datasets will have all keys, with given default value if it was not there before
                const keys = {};
                _.forEach(groups, x => {
                    _.assign(keys, this.listToSet(_.unzip(x)[0]));
                });

                _.forEach(groups, x => {
                    const curSet = this.listToSet(_.unzip(x)[0]);
                    _.forEach(keys, (val, key) => {
                        if (!(key in curSet)){
                            const curDefault = _.isFunction(missing) ?
                                missing(key, val, curSet, x) : missing;
                            x.push([key, curDefault]);
                        }
                    });
                });
            },

            mergeGroups(groups, missing){
                // [g1 => [gg1=>obj, gg2=>obj, ...], g2 => ..., ...]
                // modifies the given groups so they have same labels and fills missing for missing pieces
                const keys = {};
                _.forEach(groups, x => {
                    _.assign(keys, this.listToSet(_.keys(x)));
                });

                _.forEach(groups, grp => {
                    for(const curLabel in keys){
                        if (!(curLabel in grp)){
                            grp[curLabel] = _.isFunction(missing) ? missing(curLabel, grp) : missing;
                        }
                    }
                });
            },

            flipGroups(groups, missing){
                // transforms [g1 => [gg1=>obj, gg2=>obj, ...], g2 => ..., ...]
                // to         [gg1=> [g1=>obj, g2=>obj,... ], gg2=> [] ]
                // returns a new object
                const keys = {};
                _.forEach(groups, x => {
                    _.assign(keys, this.listToSet(_.keys(x)));
                });

                return _.reduce(keys, (acc, tmp, key) => {
                    acc[key] =
                        _.mapValues(groups, (gval, gkey) => {
                            return key in gval ? gval[key] : (_.isFunction(missing) ? missing(gval, groups) : missing);
                        });

                    return acc;
                }, {});
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

            takeMod(set, len){
                const ret = [];
                const ln = set.length;
                for(let i = 0; i<len; i++){
                    ret.push(set[i % ln]);
                }
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

