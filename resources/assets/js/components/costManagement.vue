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

                <!-- Certificate price list -->
                <div class="row" v-if="certPriceData">
                    <div class="xcol-md-12">
                        <sbox cssBox="box-success" :headerCollapse="true">
                            <template slot="title">Certificate expenses estimation</template>
                            <div class="table-responsive table-xfull" style="margin-bottom: 10px">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                    <tr>
                                        <th colspan="2">Provider</th>
                                        <th colspan="2">TLS certs</th>
                                        <th colspan="2">All issued certificates (CT)</th>
                                    </tr>
                                    <tr>
                                        <th></th>
                                        <th>Type</th>
                                        <th>Units</th>
                                        <th>Price</th>
                                        <th>Units</th>
                                        <th>Price</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    <template v-for="curDat in certPriceData">
                                        <tr>
                                            <td colspan="4"> {{ curDat[0] }} </td>
                                        </tr>

                                        <tr>
                                            <td></td>
                                            <td>DV</td>
                                            <td>{{ curDat[1].num_price[0][0] }}</td>
                                            <td>$ {{ curDat[1].num_price[0][1] }}</td>
                                            <td>{{ curDat[2].num_price[0][0] }}</td>
                                            <td>$ {{ curDat[2].num_price[0][1] }}</td>
                                        </tr>

                                        <tr>
                                            <td></td>
                                            <td>DV + Wildcard</td>
                                            <td>{{ curDat[1].num_price[1][0] }}</td>
                                            <td>$ {{ curDat[1].num_price[1][1] }}</td>
                                            <td>{{ curDat[2].num_price[1][0] }}</td>
                                            <td>$ {{ curDat[2].num_price[1][1] }}</td>
                                        </tr>

                                        <tr>
                                            <td></td>
                                            <td>EV</td>
                                            <td>{{ curDat[1].num_price[2][0] }}</td>
                                            <td>$ {{ curDat[1].num_price[2][1] }}</td>
                                            <td>{{ curDat[2].num_price[2][0] }}</td>
                                            <td>$ {{ curDat[2].num_price[2][1] }}</td>
                                        </tr>

                                        <tr>
                                            <td></td>
                                            <td>EV + Wildcard</td>
                                            <td>{{ curDat[1].num_price[3][0] }}</td>
                                            <td>$ {{ curDat[1].num_price[3][1] }}</td>
                                            <td>{{ curDat[2].num_price[3][0] }}</td>
                                            <td>$ {{ curDat[2].num_price[3][1] }}</td>
                                        </tr>

                                        <tr>
                                            <td></td>
                                            <td>Total</td>
                                            <td>{{ curDat[1].total_num }}</td>
                                            <td>$ {{ curDat[1].total_price }}</td>
                                            <td>{{ curDat[2].total_num }}</td>
                                            <td >$ {{ curDat[2].total_price }}</td>
                                        </tr>

                                    </template>

                                    </tbody>
                                </table>
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

    import VueCharts from 'vue-chartjs';
    import ToggleButton from 'vue-js-toggle-button';
    import { Bar, Line } from 'vue-chartjs';
    import Chart from 'chart.js';
    import toastr from 'toastr';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VueRouter from 'vue-router';

    Vue.use(VueEvents);
    Vue.use(VueRouter);
    Vue.use(ToggleButton);

    const router = window.VueRouter; // type: VueRouter
    export default {
        data () {
            return {
                loadingState: 0,
                dataProcessStart: 0,
                results: null,
                certIssuerTableData: null,
                certPriceData: null,

                chartColors: [
                    '#00c0ef',
                    '#f39c12',
                    '#00a65a',
                    '#f56954',
                ],
            }
        },

        computed: {
            tlsCertsIdsMap(){
                if (!this.results || !this.results.watch_to_tls_certs){
                    return {};
                }

                return Req.listToSet(_.uniq(_.flattenDeep(_.values(this.results.watch_to_tls_certs))));
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
                    return (x.id in this.tlsCertsIdsMap) || (x.valid_to_days >= -28);
                });
            },

            tlsCertIssuers(){
                return this.certIssuersGen(this.tlsCerts);
            },

            allCertIssuers(){
                return this.certIssuersGen(this.certs);
            },

        },

        mounted() {
            this.$nextTick(() => {
                this.hookup();
            })
        },

        methods: {
            refresh(){
                this.$events.fire('on-manual-refresh');
            },

            hookup(){
                setTimeout(this.loadData, 0);
            },

            transition_hook(el){
                this.recomp();
            },

            recomp(){
                this.$emit('onRecompNeeded');
            },

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
                axios.get('/home/cost-management/data')
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
            },

            processData(){
                this.$nextTick(() => {
                    console.log('Data loaded');
                    this.dataProcessStart = moment();
                    this.processResults();
                });
            },

            extendDateField(obj, key) {
                if (_.isEmpty(obj[key]) || _.isUndefined(obj[key])){
                    obj[key+'_utc'] = undefined;
                    obj[key+'_days'] = undefined;
                    return;
                }

                const utc = moment.utc(obj[key]).unix();
                obj[key+'_utc'] = utc;
                obj[key+'_days'] = Math.round(10 * (utc - moment().utc().unix()) / 3600.0 / 24.0) / 10;
            },

            processResults() {
                const curTime = moment().valueOf() / 1000.0;
                for(const [certId, cert] of Object.entries(this.results.certificates)){
                    cert.valid_to_days = Math.round(10 * (cert.valid_to_utc - curTime) / 3600.0 / 24.0) / 10;
                    cert.valid_from_days = Math.round(10 * (curTime - cert.valid_from_utc) / 3600.0 / 24.0) / 10;
                    cert.validity_sec = cert.valid_to_utc - cert.valid_from_utc;
                }

                Req.normalizeValue(this.results.certificates, 'issuer_o', {
                    newField: 'issuerOrgNorm',
                    normalizer: Req.normalizeIssuer
                });

                this.$set(this.results, 'certificates', this.results.certificates);
                this.$forceUpdate();
                this.$emit('onProcessed');
                this.loadingState = 10;

                this.$nextTick(() => {
                    const processTime = moment().diff(this.dataProcessStart);
                    console.log('Processing finished in ' + processTime + ' ms');
                    this.postLoad();
                });
            },

            postLoad(){
                this.certIssuersGraph();
            },

            certIssuersGen(certSet){
                return _.groupBy(certSet, x => {
                    return x.issuerOrgNorm;
                });
            },

            groupByCertParams(certSet){
                // certificate group [c1,c2,c3,...]
                // group by (is_ev, is_wildcard) tuple, 00,01,10,11
                return _.assign({0: [], 1: [], 2:[], 3:[]},
                    _.groupBy(certSet, x => {
                        return Number(!!x.is_ev << 1 || !!x.is_wildcard);
                    }));
            },

            groupedCostCerts(certSet){
                return _.mapValues(certSet, (val, key) => {
                    const paramGrouped = this.groupByCertParams(val);
                    const evgrpData = _.mapValues(paramGrouped, (x, xx) => {
                        return [
                            // cert count
                            _.size(x),
                            // cert sum price
                            _.reduce(x, (acc, val, key) => {
                                return _.isNumber(val.price) ? acc + val.price : acc;
                            }, 0.0)
                        ];
                    });


                    return {
                        'evg': paramGrouped,
                        'num_price': evgrpData,
                        'total_num': _.sumBy(_.values(evgrpData), x => { return x[0]; }),
                        'total_price': _.sumBy(_.values(evgrpData), x => { return x[1]; }),
                    }
                });
            },

            certIssuersGraph(){
                // CA -> sub table normal, EV, wildcard
                // group by vendor, group by ev, wildcard values
                // View idea:
                //   - View per issuer org (grouping)
                //   - per issuer: # of normal, ev, wildcard certs, ev+wild;
                //   -     numbers + price
                //   -     total sum of numbers + price

                // groupedCostCerts(allCertIssuers) -> [issuer -> [ [00] -> [], [01] -> [], ... ], ...]
                const tlsPriceData = this.groupedCostCerts(this.tlsCertIssuers);
                const allPriceData = this.groupedCostCerts(this.allCertIssuers);

                ReqD.mergeGroups([tlsPriceData, allPriceData], {
                    'evg': [],
                    'num_price': {0:[0,0], 1:[0,0], 2:[0,0], 3:[0,0] },
                    'total_num': 0,
                    'total_price': 0,
                });

                const tlsPriceDataPairs = _.toPairs(tlsPriceData);
                const allPriceDataPairs = _.toPairs(allPriceData);

                this.certPriceData = _.sortBy(
                    ReqD.mergeGroupStatValues([tlsPriceDataPairs, allPriceDataPairs]),
                    x => {
                        return -1 * _.max(_.map(_.tail(x)), xx => {
                            return xx.total_num;
                        });
                    });

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
                                backgroundColor: this.chartColors[0],
                                //backgroundColor: Req.takeMod(this.chartColors, tlsIssuerUnz[0].length),
                                label: 'Detected on servers'
                            },
                            {
                                data: allIssuerUnz[1],
                                backgroundColor: this.chartColors[2],
                                //backgroundColor: Req.takeMod(this.chartColors, allIssuerUnz[0].length),
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

            cleanResults(){
                this.results = null;
                this.loadingState = 0;
                this.$emit('onReset');
            },



        },
    }
</script>

