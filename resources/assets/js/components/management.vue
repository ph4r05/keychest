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
                            <h3>{{dnsFailedLookups.length+ tlsErrors.length}} / {{ numWatches }}</h3>
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
                        Key Management Report - {{ (new Date()).toLocaleString("en-us",{'day':'numeric','month':'short',
                        'year':'numeric', 'hour':'numeric','minute':'numeric'}) }}
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
            <div class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-success" :headerCollapse="true">
                        <template slot="title">Yearly renewal calendar</template>
                        <p>
                            The following two charts provide information about the effort needed in the next 12 months to
                            keep all your certificates valid. The first chart shows certificates we verified directly
                            when scanning your servers.
                            <br>
                            <i>Note: you can click an chart labels to hide/unhide types of certificates.</i>
                        </p>
                        <div class="form-group">
                            <canvas id="columnchart_certificates_js" style="width:100%; height: 350px"></canvas>
                        </div>

                        <div class="form-group">
                            <canvas id="columnchart_certificates_all_js" style="width:100%; height: 350px"></canvas>
                        </div>
                        <p>

                            <i>Note: The number of renewals for certificates, notably Let&#39;Encrypt certificates, valid
                                for less than 12 months, is estimated for months beyond their maximum validity.</i>
                            <br/><br/>
                            You may want to check that all certificates are legitimate if:
                        </p>
                        <ul>
                            <li>there is a difference between the two charts;</li>
                            <li>all monitored servers are running; and</li>
                            <li>there is no CDN/ISP certificate in the first chart.</li>
                        </ul>
                        <p>
                            The "Informational" part of this dashboard lists all certificates sorted by expiration date
                            so you can easily find a complete list of relevant certificates with expiry dates in the
                            given month.
                        </p>
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
                    <sbox cssBox="box-danger" :collapsed="true" :headerCollapse="true">
                        <template slot="title">DNS configuration issues ({{dnsFailedLookups.length}})</template>
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
                <sbox cssBox="box-success" :headerCollapse="true">
                    <template slot="title">Renewals due in next 28 days</template>
                    <p>Watch carefully dates in the following table to prevent downtime on your servers. Certificates expired
                    more than 28 days ago are excluded.</p>
                    <div class="col-md-8">
                        <div class="table-responsive table-xfull">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th colspan="2">Renew before</th>
                                    <th>Server names</th>
                                    <th>Last update</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="grp in imminentRenewalCerts">
                                    <td colspan="2" v-bind:class="grp[0].planCss.tbl" v-if="momentu(grp[0].valid_to_utc * 1000.0)<momentu()">
                                        SERVER DOWN since {{ momentu(grp[0].valid_to_utc * 1000.0).fromNow() }} </td>
                                    <td colspan="2" v-bind:class="grp[0].planCss.tbl" v-else="">
                                        {{ new Date(grp[0].valid_to_utc * 1000.0).toLocaleDateString() }}
                                        ({{ momentu(grp[0].valid_to_utc * 1000.0).fromNow() }}) </td>
                                    <td v-bind:class="grp[0].planCss.tbl">
                                        <ul class="coma-list" v-if="len(getCertHostPorts(grp)) > 0">
                                            <li v-for="domain in getCertHostPorts(grp)">{{ domain }}</li>
                                        </ul>
                                        <span v-else="">No domains found</span>
                                    </td>
                                    <td v-bind:class="grp[0].planCss.tbl">{{new Date(grp[0].last_scan_at_utc * 1000.0).toLocaleString()}}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <canvas id="imminent_renewals_js" style="width: 100%; height: 300px;"></canvas>
                    </div>
                </sbox>
                </div>
            </div>

            <!-- Expiring domains -->
            <div v-if="showExpiringDomains" class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-success" :headerCollapse="true">
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
                                    {{ momentu(cur_whois.expires_at_utc * 1000.0).fromNow() }} </td>
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
                    <sbox cssBox="box-warning" :headerCollapse="true">
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
                    <sbox cssBox="box-success" :headerCollapse="true">
                        <template slot="title">Certificate overview</template>
                        <div class="form-group">
                            <p>
                                Certificates in your inventory can be managed by third-party (CDN or ISP). You are
                                responsible for renewing certificate issued by Let&#39;s Encrypt (short validity
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
                    <sbox cssBox="box-success" :headerCollapse="true">
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
                            <canvas id="pie_cert_domains" style="height: 400px;"></canvas>
                        </div>
                        <!--<div class="col-md-6">-->
                            <!--<canvas id="pie_cert_domains_tld" style=" height: 400px;"></canvas>-->
                        <!--</div>-->
                    </sbox>
                </div>
            </div>

            <!-- Certificate list -->
            <a name="certs"></a>
            <div class="row">
                <div class="xcol-md-12">
                    <sbox cssBox="box-primary" :collapsed="true" :headerCollapse="true">
                        <template slot="title">Certificates under your management</template>
                        <div class="form-group">
                        <p>This is a list of all certificates that you control and are responsible for renewals.
                            You can choose to see only certificates correctly installed on your server,
                            or all certificates issued to your servers.</p>
                            <toggle-button v-model="includeNotVerified" id="chk-include-notverified"
                                           color="#00a7d7"
                                           disabled="disabled"
                                           :labels="{checked: 'On', unchecked: 'Off'}"
                            ></toggle-button>
                        <label for="chk-include-notverified">Include certificates not verified from your servers</label>
                        </div>
                        <div class="table-responsive table-xfull">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>Server names</th>
                                    <th>Issuer</th>
                                    <th colspan="2">Renew / plan renewal</th>
                                </tr>
                                </thead>

                                <tbody>
                                <tr v-for="cert in sortExpiry(tlsCerts)" v-if="cert.planCss">
                                    <td v-bind:class="cert.planCss.tbl">
                                        <span class="hidden">
                                            ID: {{ cert.id }}
                                            CNAME: {{ cert.cname }}
                                        </span>
                                        <ul class="domain-list">
                                            <li v-for="domain in cert.watch_hosts">
                                                <template v-if="cert.cname === domain">{{ domain }} <small><em>(CN)</em></small></template>
                                                <template v-else="">{{ domain }}</template>
                                            </li>
                                        </ul>
                                    </td>
                                    <td v-bind:class="cert.planCss.tbl">{{ cert.issuerOrgNorm }}</td>
                                    <td v-bind:class="cert.planCss.tbl">{{ cert.valid_to }}</td>
                                    <td v-bind:class="cert.planCss.tbl"
                                        v-if="(momentu(cert.valid_to)<momentu())&&(len(cert.watch_hosts)<2)">
                                        SERVER DOWN since {{ momentu(cert.valid_to).fromNow() }}</td>
                                    <td v-bind:class="cert.planCss.tbl"
                                        v-else-if="(momentu(cert.valid_to)<momentu())&&(len(cert.watch_hosts)>1)">
                                        SERVERS DOWN since {{ momentu(cert.valid_to).fromNow() }}</td>
                                    <td v-bind:class="cert.planCss.tbl"
                                        v-else="">{{ momentu(cert.valid_to).fromNow() }}</td>
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

                graphLibLoaded: false,
                graphsRendered: false,
                graphDataReady: false,

                certIssuerTableData: null,
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
                const [graphCrtTlsData, graphCrtAllData] = charts.plannerConfig(this.crtTlsMonth, this.crtAllMonth);
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
                    util.invMaxTail);

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

