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
                <strong>Success!</strong> Computation finished.
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

                            <p>
                                And some text.
                            </p>
                                <table>
                                    <tr>
                                        <td>Hourly cost of your IT support for  certificate management&nbsp;&nbsp;</td>
                                        <td><div class="input-group input-group-sm input-group-inline valign-middle">
                                            <span class="input-group-addon"><i class="glyphicon glyphicon-usd"></i></span>
                                            <input type="text" class="form-control" size="3"
                                                   title="hourly payment for cert management"
                                                   v-model="hourlyPay">
                                        </div></td>
                                    </tr>
                                </table>
                                <p>
                                    bla
                                </p>

                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                    <tr>
                                        <th>Provider</th>
                                        <th>Type</th>
                                        <th align="center">Units in use</th>
                                        <th align="center">Units unused</th>
                                        <th align="center">Price</th>
                                        <th align="center">KeyChest managed</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    <template v-for="(curDat, datIdx) in certPriceData">
                                        <template v-for="idx in 6">
                                            <tr v-if="(curDat[1].num_price[idx-1][0]+curDat[2].num_price[idx-1][0]>0)" >
                                                <td>{{ curDat[0] }}</td>
                                                <td>{{ certTypeLabels[idx-1] }}</td>
                                                <td align="right">{{ curDat[1].num_price[idx-1][0] }}</td>
                                                <td align="right">{{ curDat[2].num_price[idx-1][0] - curDat[1].num_price[idx-1][0] }}</td>
                                                <td align="right">${{ formatTableNumber(curDat[2].num_price[idx-1][1]) }}</td>
                                                <td align="center">
                                                    <input disabled="disabled" type="checkbox" title="KeyChest managed"
                                                           v-model="curDat[2].mods[idx-1].kcman">
                                                </td>
                                            </tr>
                                        </template>
                                    </template>
<!--                                    <tr>
                                        <td colspan="4"><b>Current annual certificate cost estimate</b></td>
                                        <td align="right">$ {{ certsCostTotal }} </td>
                                        <td></td>
                                    </tr>-->
                                    <tr>
                                        <td colspan="2">Estimate of IT support time (labour cost)</td>
                                        <td colspan="2" align="right">{{ hourlyPay }} hours</td>
                                        <td align="right">${{ certsOpsCost }} </td>
                                        <td></td>
                                    </tr>

                                    <tr>
                                        <th colspan="4">Current annual cost of certificate management </th>
                                        <td align="right"><b>${{ formatTableNumber(totalCostWithoutKc) }}</b></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="7"></td>
                                    </tr>
                                    <tr>
                                        <th colspan="6">KeyChest managed certificates</th>
                                    </tr>
                                    <tr>
                                        <td colspan="4"><i>Item</i></td>
                                        <td align="center"><i>Cost</i></td>
                                        <td align="center"><i>Saving</i></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4">Certificate cost</td>
                                        <td align="right">${{ formatTableNumber(totalCostWithKc - kcLicense) }} </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4">KeyChest License</td>
                                        <td align="right">${{ formatTableNumber(kcLicense) }} </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th colspan="4">Total cost with KeyChest (savings of {{ formatFloat(savingPercent) }}%) </th>
                                        <td align="right"><b>${{ formatTableNumber(totalCostWithKc) }} </b> </td>
                                        <td align="center"><b>${{ formatTableNumber(totalSaving) }} </b></td>
                                    </tr>

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
    import numeral from 'numeral';
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
                hourlyPay: 25,
                results: null,
                certIssuerTableData: null,
                certPriceData: null,
                certTypeLabels: ['Domain Validated', 'Wildcard, Domain Validated', 'Org. Validated',
                    'Wildcard, Org. Validated', 'Extended Validated', 'Wildcard, Exended Validated'],

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

            kcLicense(){
                return 4000.;  // may depend on another variables (e.g. number of managed certs)
            },

            certsCostUnused(){
                return _.isEmpty(this.certPriceData) ? 0 : _.reduce(this.certPriceData, (acc, val, key) => {
                    return acc + (val[2].total_price - val[1].total_price);  // using CT - TLS detected
                }, 0.0);
            },

            certsCostTotal(){
                return _.isEmpty(this.certPriceData) ? 0 : _.reduce(this.certPriceData, (acc, val, key) => {
                    return acc + val[2].total_price;  // using CT
                }, 0.0);
            },

            certsOpsCount(){
                return _.isEmpty(this.certPriceData) ? 0 :  _.reduce(this.certPriceData, (acc, val, key) => {
                    return acc + val[1].total_num;  // using TLS scan for management numbers
                }, 0.0);
            },

            certsOpsCost(){
                return this.certsOpsCount * this.hourlyPay * 0.5;
            },

            totalCostWithoutKc(){
                return this.certsCostTotal + this.certsOpsCost;
            },

            certsUnmanagedCost(){
                // Cost with managed by KC - subtract those managed by KC
                return _.isEmpty(this.certPriceData) ? 0 : _.reduce(this.certPriceData, (acc, val, key) => {
                    return acc + _.reduce(val[1].num_price, (subAcc, subVal, subKey) => {
                            return subAcc + subVal[1] * !val[2].mods[subKey].kcman; // managed? then 0 cost
                    }, 0.0);
                }, 0.0);
            },

            certsManagedOpsCost(){
                return _.isEmpty(this.certPriceData) ? 0 : _.reduce(this.certPriceData, (acc, val, key) => {
                    return acc + _.reduce(val[1].num_price, (subAcc, subVal, subKey) => {
                        return subAcc + subVal[0] * !val[2].mods[subKey].kcman; // managed? then 0 units
                    }, 0.0);
                }, 0.0) * this.hourlyPay * 0.5;
            },

            totalCostWithKc(){
                return this.kcLicense + this.certsManagedOpsCost + this.certsUnmanagedCost;
            },

            savingPercent(){
                return 100.0 * (1 - this.totalCostWithKc/this.totalCostWithoutKc);
            },

            totalSaving(){
                return this.totalCostWithoutKc - this.totalCostWithKc;
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

            formatFloat(x){
                return (numeral(x).format('0'));
            },

            formatTableNumber(x){
                return ((10 * Math.floor((x)/10)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
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
                this.certPricing();
                this.certIssuersGraph();
            },

            certIssuersGen(certSet){
                return _.groupBy(certSet, x => {
                    return x.issuerOrgNorm;
                });
            },

            groupByCertParams(certSet){
                // certificate group [c1,c2,c3,...]
                // group by: 0th bit - wildcard flag, rest - validation (ev=2, ov=1, dv=0)
                return _.assign({0: [], 1: [], 2:[], 3:[], 4:[], 5:[]},
                    _.groupBy(certSet, x => {
                        // due to inclusive property EV contains also OV.
                        const validation = (!!x.is_ev) ? 2 : ((!!x.is_ov) ? 1 : 0);
                        const is_wildcard = (!!x.is_cn_wildcard) || (!!x.is_alt_wildcard);
                        return Number(is_wildcard | (validation << 1));
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
                    const model = _.mapValues(paramGrouped, (x, xx) => {
                        return {
                            'kcman': xx < 4
                        };
                    });

                    return {
                        'evg': paramGrouped,
                        'num_price': evgrpData,
                        'mods': model,
                        'total_num': _.sumBy(_.values(evgrpData), x => { return x[0]; }),
                        'total_price': _.sumBy(_.values(evgrpData), x => { return x[1]; }),
                    }
                });
            },

            certPricing(){
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
                    'num_price': {0:[0,0], 1:[0,0], 2:[0,0], 3:[0,0], 4:[0,0], 5:[0,0] },
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
            },

            certIssuersGraph(){
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
            },

            cleanResults(){
                this.results = null;
                this.loadingState = 0;
                this.$emit('onReset');
            },



        },
    }
</script>

