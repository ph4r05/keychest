<template>
    <div v-if="showImminentRenewals" class="row">
        <div class="xcol-md-12">
            <sbox cssBox="box-success" :headerCollapse="true">
                <template slot="title">Renewals due in next 28 days</template>
                <p>Watch carefully dates in the following table to prevent downtime on your servers. Certificates expired
                    more than 28 days ago are excluded.</p>
                <div class="col-md-8">
                    <imminent-renewals-table :imminent-renewal-certs="imminentRenewalCerts"/>
                </div>
                <div class="col-md-4">
                    <canvas ref="imminent_renewals" style="width: 100%; height: 300px;"></canvas>
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
    import util from './code/util';
    import charts from './code/charts';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VueScrollTo from 'vue-scrollto';
    import VeeValidate from 'vee-validate';
    import { mapFields } from 'vee-validate';

    import DashboardCertRenewalsTable from './tables/CertRenewalsTable';

    Vue.use(VueEvents);
    Vue.use(VueScrollTo);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    export default {
        name: 'imminent-renewals',

        components: {
            'imminent-renewals-table': DashboardCertRenewalsTable,
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
        },

        computed: {
            imminentRenewalCerts(){
                return util.imminentRenewalCerts(this.tlsCerts);
            },

            showImminentRenewals(){
                return _.reduce(this.tlsCerts, (acc, cur) => {
                    return (acc + (cur.valid_to_days <= 28 && cur.valid_to_days >= -28));
                }, 0) > 0;
            },

            week4renewals(){
                return util.week4renewals(this.tlsCerts);
            },

            week4renewalsCounts(){
                return util.week4renewalsCounts(this.tlsCerts);
            },

            graphData() {
                return !this.showImminentRenewals ? {} :  charts.week4renewConfig(this.week4renewalsCounts);
            },
        },

        watch: {
            graphData(newVal, oldVal) {
                if (newVal !== oldVal || !newVal) {
                    this.week4renewGraph();
                }
            }
        },

        data: function() {
            return {
                chart: null,
            };
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
                this.week4renewGraph();
            },

            /**
             * Renders the graph
             */
            week4renewGraph(){
                setTimeout(() => {
                    this.chart = charts.chartCreateUpdate(this.chart, this.$refs.imminent_renewals, this.graphData);
                }, 250);
            },
        },
    }
</script>

<style scoped>

</style>
