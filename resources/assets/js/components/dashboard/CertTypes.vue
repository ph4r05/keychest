<template>
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
                    <canvas ref="cert_types" style="width: 100%; height: 350px;"></canvas>
                </div>
            </sbox>
        </div>
    </div>
</template>

<script>
    import _ from 'lodash';
    import moment from 'moment';
    import pluralize from 'pluralize';
    import Chart from 'chart.js';

    import Req from 'req';
    import util from './code/util';
    import charts from './code/charts';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VueScrollTo from 'vue-scrollto';
    import VeeValidate from 'vee-validate';
    import { mapFields } from 'vee-validate';

    import DashboardCertDomainsTable from './tables/CertDomainsTable';

    Vue.use(VueEvents);
    Vue.use(VueScrollTo);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    export default {
        name: 'cert-types',

        components: {
            'cert-domains-table': DashboardCertDomainsTable,
        },

        props: {
            /**
             * Input to display
             */
            certs: {
                type: Array,
                default() {
                    return []
                },
            },

            tlsCerts: {
                type: Array,
                default() {
                    return []
                },
            },

            cdnCerts: {
                type: Object,
                default() {
                    return {}
                },
            },
        },

        computed: {
            certTypesStats(){
                return this.certTypes(this.tlsCerts);
            },

            certTypesStatsAll(){
                return this.certTypes(this.certs);
            },

            graphData() {
                return charts.certTypesConfig(this.certTypesStatsAll, this.certTypesStats);
            },
        },

        watch: {
            graphData(newVal, oldVal) {
                if (newVal !== oldVal || !newVal) {
                    this.certTypesGraph();
                }
            }
        },

        mounted() {
            this.$nextTick(() => {
                this.hookup();
            })
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
            certIssuer: util.certIssuer,

            /**
             * Executed when mounted.
             */
            hookup(){
                this.certTypesGraph();
            },

            /**
             * Renders the graph
             */
            certTypesGraph(){
                new Chart(this.$refs.cert_types, this.graphData);
            },

            certTypes(certSet){
                return util.certTypes(certSet, this.cdnCerts);
            },
        },
    }
</script>

<style scoped>

</style>
