<template>
    <div>
        <div class="row" v-if="anyIncident">
            <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-exclamation-circle"></i></span>
                <div class="info-box-content info-box-label">
                    Incidents
                </div>
            </div>
        </div>

        <!-- DNS lookup fails -->
        <div v-if="len(dnsFailedLookups) > 0" class="row">
            <div class="xcol-md-12">
                <sbox cssBox="box-danger" :collapsed="true" :headerCollapse="true">
                    <template slot="title">DNS configuration issues ({{ len(dnsFailedLookups) }})</template>
                    <p>Please check if the following domain names are correct. You may also need to verify
                        your DNS configuration at your DNS registrar and at your DNS servers.</p>

                    <dns-errors-table :dnsFailedLookups="dnsFailedLookups"/>
                </sbox>
            </div>
        </div>

        <!-- TLS connection fails -->
        <div v-if="len(tlsErrors) > 0" class="row">
            <div class="xcol-md-12">
                <sbox cssBox="box-danger" :collapsed="true" :headerCollapse="true">
                    <template slot="title">Unreachable servers ({{ len(tlsErrors) }})</template>
                    <p>We failed to connect to one or more servers using TLS protocol.</p>

                    <tls-errors-table :tlsErrors="tlsErrors"/>
                </sbox>
            </div>
        </div>

        <!-- TLS trust errors -->
        <div v-if="len(tlsInvalidTrust) > 0" class="row">
            <div class="xcol-md-12">
                <sbox cssBox="box-danger" :collapsed="true" :headerCollapse="true">
                    <template slot="title">Servers with configuration errors ({{ len(tlsInvalidTrust) }})</template>
                    <p>We detected security or configuration problems at following servers</p>

                    <tls-trust-errors-table :tlsInvalidTrust="tlsInvalidTrust"/>
                </sbox>
            </div>
        </div>

        <!-- TLS hostname errors -->
        <div v-if="len(tlsInvalidHostname) > 0" class="row">
            <div class="xcol-md-12">
                <sbox cssBox="box-danger" :collapsed="true" :headerCollapse="true">
                    <template slot="title">Unused, default, or incorrect certificates ({{ len(tlsInvalidHostname) }})</template>
                    <p>Service name (URL) is different from the name in certificates</p>

                    <tls-invalid-hosts-table :tlsInvalidHostname="tlsInvalidHostname"/>
                </sbox>
            </div>
        </div>

        <!-- TLS expired certificates -->
        <div v-if="len(expiredCertificates) > 0" class="row">
            <div class="xcol-md-12">
                <sbox cssBox="box-danger" :headerCollapse="true">
                    <template slot="title">Servers with expired certificates ({{ len(expiredCertificates) }})</template>
                    <p>Clients can't connect to following servers due to expired certificates.</p>

                    <cert-expired-table :expiredCertificates="expiredCertificates"/>
                </sbox>
            </div>
        </div>
    </div>
</template>

<script>
    import _ from 'lodash';
    import moment from 'moment';
    import pluralize from 'pluralize';

    import Req from 'req';
    import util from './code/util';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VueScrollTo from 'vue-scrollto';
    import VeeValidate from 'vee-validate';
    import { mapFields } from 'vee-validate';

    import DashboardDnsErrorsTable from './tables/DnsErrorsTable';
    import DashboardTlsErrorsTable from './tables/TlsErrorsTable';
    import DashboardTlsTrustErrorsTable from './tables/TlsTrustErrorsTable';
    import DashboardTlsInvalidHostsErrorsTable from './tables/TlsInvalidHostsTable';
    import DashboardCertExpiredTable from './tables/CertExpiredTable';

    Vue.use(VueEvents);
    Vue.use(VueScrollTo);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    export default {
        name: 'incidents',

        components: {
            'dns-errors-table': DashboardDnsErrorsTable,
            'tls-errors-table': DashboardTlsErrorsTable,
            'tls-trust-errors-table': DashboardTlsTrustErrorsTable,
            'tls-invalid-hosts-table': DashboardTlsInvalidHostsErrorsTable,
            'cert-expired-table': DashboardCertExpiredTable,
        },

        props: {
            /**
             * Input to display
             */
            dnsFailedLookups: {
                type: Array,
                default() {
                    return []
                },
            },

            tlsErrors: {
                type: Array,
                default() {
                    return []
                },
            },

            tlsInvalidTrust: {
                type: Array,
                default() {
                    return []
                },
            },

            tlsInvalidHostname: {
                type: Array,
                default() {
                    return []
                },
            },

            expiredCertificates: {
                type: Array,
                default() {
                    return []
                },
            },
        },

        computed: {
            anyIncident() {
                return this.len(this.dnsFailedLookups) > 0 ||
                    this.len(this.tlsErrors) > 0 ||
                    this.len(this.expiredCertificates) > 0 ||
                    this.len(this.tlsInvalidTrust) > 0 ||
                    this.len(this.tlsInvalidHostname) > 0;
            },
        },

        methods: {
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
        },
    }
</script>

<style scoped>

</style>
