<template>
    <div class="row" v-if="certIssuerTableData">
        <div class="xcol-md-12">
            <sbox cssBox="box-success" :headerCollapse="true">
                <template slot="title">Number of certificates per issuer</template>

                <cert-issuer-table :certIssuerTableData="certIssuerTableData"/>

                <div class="form-group">
                    <canvas ref="cert_issuers" style="width: 100%; height: 500px;"></canvas>
                </div>
            </sbox>
        </div>
    </div>
</template>

<script>
    import _ from 'lodash';
    import moment from 'moment';
    import pluralize from 'pluralize';

    import Req from 'req';
    import ReqD from 'req-data';
    import util from './code/util';
    import charts from './code/charts';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VueScrollTo from 'vue-scrollto';
    import VeeValidate from 'vee-validate';
    import { mapFields } from 'vee-validate';

    import DashboardCertIssuerTable from './tables/CertIssuerTable';

    Vue.use(VueEvents);
    Vue.use(VueScrollTo);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    export default {
        name: 'cert-issuers',

        components: {
            'cert-issuer-table': DashboardCertIssuerTable,
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
            tlsCertIssuers(){
                return util.certIssuersGen(this.tlsCerts);
            },

            allCertIssuers(){
                return util.certIssuersGen(this.certs);
            },

            graphPreData() {
                const tlsIssuerStats = ReqD.groupStats(this.tlsCertIssuers, 'count');
                const allIssuerStats = ReqD.groupStats(this.allCertIssuers, 'count');
                ReqD.mergeGroupStatsKeys([tlsIssuerStats, allIssuerStats]);
                ReqD.mergedGroupStatSort([tlsIssuerStats, allIssuerStats], ['1', '0'], ['desc', 'asc']);
                return [tlsIssuerStats, allIssuerStats];
            },

            certIssuerTableData() {
                return _.sortBy(
                    ReqD.mergeGroupStatValues([
                        (this.graphPreData)[0],
                        (this.graphPreData)[1]
                    ]),
                    util.invMaxTail);
            },

            graphData() {
                const tlsIssuerUnz = _.unzip((this.graphPreData)[0]);
                const allIssuerUnz = _.unzip((this.graphPreData)[1]);
                return charts.certIssuerConfig(allIssuerUnz, tlsIssuerUnz);
            },
        },

        watch: {
            graphData(newVal, oldVal) {
                if (newVal !== oldVal || !newVal) {
                    this.certIssuersGraph();
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
                this.certIssuersGraph();
            },

            /**
             * Renders the graph
             */
            certIssuersGraph(){
                setTimeout(() => {
                    new Chart(this.$refs.cert_issuers, this.graphData);
                }, 1000);
            },

            /**
             * Helper method for generating dataset
             * @param certSet
             * @returns {*|Array}
             */
            certTypes(certSet){
                return util.certTypes(certSet, this.cdnCerts);
            },
        },
    }
</script>

<style scoped>

</style>
