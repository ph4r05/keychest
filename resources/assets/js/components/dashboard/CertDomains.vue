<template>
    <div class="row">
        <div class="xcol-md-12">
            <sbox cssBox="box-primary" :headerCollapse="true">
                <template slot="title">Number of server names in SAN certificates</template>
                <p>Certificates can be used for multiple servers (domain names).
                    The table shows how many servers can use a certain certificate.
                    This information has an impact on the cost of certificats, if there issuance
                    is not free.</p>

                <cert-domains-table :certDomainsTableData="certDomainsTableData"/>

                <div class="col-md-12">
                    <canvas ref="cert_domains" style="height: 400px;"></canvas>
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

    import DashboardCertDomainsTable from './tables/CertDomainsTable';

    Vue.use(VueEvents);
    Vue.use(VueScrollTo);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    export default {
        name: 'cert-domains',

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
        },

        computed: {
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

            graphData() {
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

                return [graphCertDomains, undefined];
            },
        },

        watch: {
            graphData(newVal, oldVal) {
                if (newVal !== oldVal || !newVal) {
                    this.certDomainsGraph();
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
                this.certDomainsGraph();
            },

            /**
             * Renders the graph
             */
            certDomainsGraph(){

                setTimeout(() => {
                    new Chart(this.$refs.cert_domains, (this.graphData)[0]);
                    // new Chart(this.$refs.cert_domains_tld, (this.graphData)[1]);
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
