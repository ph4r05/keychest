<template>
    <div class="row">
        <div class="xcol-md-12">
            <sbox cssBox="box-success" :headerCollapse="true">
                <template slot="title">Yearly renewal calendar</template>
                <p>
                    The following two charts provide information about the effort needed in the next 12 months
                    to
                    keep all your certificates valid. The first chart shows certificates we verified directly
                    when scanning your servers.
                    <br>
                    <i>Note: you can click an chart labels to hide/unhide types of certificates.</i>
                </p>

                <div class="form-group">
                    <canvas ref="chart_certs_tls" style="width:100%; height: 350px"></canvas>
                </div>

                <div class="form-group">
                    <canvas ref="chart_certs_all" style="width:100%; height: 350px"></canvas>
                </div>

                <p>
                    <i>Note: The number of renewals for certificates, notably Let&#39;s Encrypt certificates,
                        valid for less than 12 months, is estimated for months beyond their maximum validity.</i>
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

    Vue.use(VueEvents);
    Vue.use(VueScrollTo);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    export default {
        name: "cert-planner",

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
            crtTlsMonth(){
                return this.monthDataGen(_.filter(this.tlsCerts, o => {
                    return o.valid_to_days >= 0 && o.valid_to_days < 365; }));
            },

            crtAllMonth() {
                return this.monthDataGen(_.filter(this.certs, o => {
                    return o.valid_to_days >= 0 && o.valid_to_days < 365; }))
            },

            graphData(){
                return charts.plannerConfig(this.crtTlsMonth, this.crtAllMonth);
            },
        },

        watch: {
            graphData(newVal, oldVal) {
                if (newVal !== oldVal || !newVal) {
                    this.plannerGraph();
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
                this.plannerGraph();
            },

            /**
             * Renders the planner graph
             */
            plannerGraph(){
                const [graphCrtTlsData, graphCrtAllData] = this.graphData;
                if (graphCrtTlsData) {
                    new Chart(this.$refs.chart_certs_tls, graphCrtTlsData);
                    new Chart(this.$refs.chart_certs_all, graphCrtAllData);
                }
            },

            /**
             * Helper method for generating dataset
             * @param certSet
             * @returns {*|Array}
             */
            monthDataGen(certSet){
                return util.monthDataGen(certSet, {'cdnCerts': this.cdnCerts});
            },
        },
    }
</script>

<style scoped>

</style>
