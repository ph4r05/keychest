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
                        <sbox cssBox="box-primary" :headerCollapse="true">
                            <template slot="title">Certificate Cost</template>
                            <div class="table-responsive table-xfull" style="margin-bottom: 10px">

                                <p class="tc-onyx">
                                    The page demonstrates an estimated annual cost of the certificates in your KeyChest account.
                                    As the pricing of existing certificate providers is complex and may include tailored
                                    discounts, we use basic prices available on their websites.
                                </p>
                                <p class="tc-onyx">
                                    We also estimate the cost of IT support to renew certificates using a simple assumption
                                    that one renewal takes about 30 minutes from start to finish.
                                </p>
                                <table>
                                    <tr>
                                        <td><div class="input-group input-group-sm input-group-inline valign-middle">
                                            <span class="input-group-addon tc-rich-electric-blue">Adjust the hourly rate of your IT support
                                                for  certificate management in US$</span>
                                            <b><input type="text" class="form-control" size="3"
                                                   title="hourly rate of certificate management support"
                                                   v-model="hourlyPay"></b>
                                        </div></td>
                                    </tr>
                                </table>
                                <br/>
                                <table class="table table-bordered table-striped table-hover tc-onyx">
                                    <thead>
                                    <tr class="tc-rich-electric-blue">
                                        <td colspan="7"><b>Your current cost - based on undiscounted prices and
                                            KeyChest monitoring</b><br/>
                                        (all prices are in US dollars, numbers may not add up due to rounding)</td>
                                    </tr>
                                    <tr >
                                        <td colspan="2"><b>Cost item</b></td>
                                        <td align="center"><b>Units in use</b></td>
                                        <td align="center"><b>Units unused</b></td>
                                        <td align="center"><b>Cost</b></td>
                                        <td align="center"><b>In KeyChest cost</b></td>
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

                                    <tr class="tc-rich-electric-blue">
                                        <td colspan="2" class="tc-onyx">Subtotal for certificates only</td>
                                        <td align="right">${{ formatTableNumber(certsCostUsed) }}</td>
                                        <td align="right">${{ formatTableNumber(certsCostUnused) }}</td>
                                        <td align="right">${{ formatTableNumber(certsCostTotal) }}</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="7"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">IT support time (labour cost)</td>
                                        <td colspan="2" align="right">{{ certsOpsHours }} hours</td>
                                        <td align="right">${{ formatTableNumber(certsOpsCost) }} </td>
                                        <td></td>
                                    </tr>

                                    <tr>
                                        <th colspan="4">Your annual cost of certificate management </th>
                                        <td align="right" class="tc-rich-electric-blue"><b>${{ formatTableNumber(totalCostWithoutKc) }}</b></td>
                                        <td></td>
                                    </tr>
                                    </tbody>
                                </table>

                                <table class="tc-onyx table table-bordered table-striped table-hover">
                                    <thead>
                                    <tr class="tc-rich-electric-blue">
                                        <td colspan="6" ><b>Certificate management with KeyChest</b><br/>
                                            (all prices are in US dollars, numbers may not add up due to rounding)</td>
                                    </tr>
                                    <tr style="font-weight: bold;">
                                        <td colspan="4">Item</td>
                                        <td align="center">Cost</td>
                                        <td align="center">Saving</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td colspan="4">Unused certificate</td>
                                        <td align="right">$0</td>
                                        <td align="right">${{ formatTableNumber(certsCostUnused) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4">Certificate cost + labor </td>
                                        <td align="right">${{ formatTableNumber(totalCostWithKc - kcLicense) }} </td>
                                        <td align="right">${{ formatTableNumber(certsCostUsed-certsKcNonmanagedCost + certsKcManagedOpsSaving) }} </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4">KeyChest License</td>
                                        <td align="right">${{ formatTableNumber(kcLicense) }} </td>
                                        <td></td>
                                    </tr>
                                    <tr v-if="savingPercent>0">
                                        <th colspan="4">Cost saving</th>
                                        <td align="right"></td>
                                        <td align="right" class="tc-rich-electric-blue"><b>${{ formatTableNumber(totalSaving + kcLicense) }} </b></td>
                                    </tr>
                                    <tr v-if="savingPercent>0">
                                        <th colspan="4">Total cost with KeyChest</th>
                                        <td align="right" class="tc-rich-electric-blue"><b>${{ formatTableNumber(totalCostWithKc) }} </b> </td>
                                        <td align="right" class="tc-rich-electric-blue"><b>{{ formatFloat(savingPercent) }}%</b></td>
                                    </tr>
                                    <tr v-else="">
                                        <th colspan="4">Total cost with KeyChest</th>
                                        <td align="right" class="tc-rich-electric-blue"><b>${{ formatTableNumber(totalCostWithKc) }} </b> </td>
                                        <td align="right"><b></b></td>
                                    </tr>

                                    </tbody>
                                </table>
                                <p class="tc-onyx">
                                    The KeyChest cost includes an initial configuration of a renewal agent to provide
                                    the required functionality. Please get in touch using the form below, if you require
                                    a quotation.
                                </p>
                                <p><i>
                                    Disclaimer: The calculation is intended for an estimate only, with the purpose of allowing you
                                    to consider the KeyChest service. The actual KeyChest license cost will be subject
                                    to a final technical and  security assessment of your requirements.  We make no
                                    representations and warranties whatsoever.
                                </i>
                                </p>

                            </div>
                        </sbox>
                    </div>
                </div>


                <iframe title="KeyChest cost feedback" class="freshwidget-embedded-form" id="freshwidget-embedded-form-4"
                        src="https://enigmabridge.freshdesk.com/widgets/feedback_widget/new?&SubmitTitle=Send+Now&widgetType=embedded&screenshot=no&searchArea=no"
                        scrolling="no" height="410px" width="100%" frameborder="0" >
                </iframe>
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
    import util from './dashboard/code/util';

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
                excludeCdns: true,

                results: null,
                certIssuerTableData: null,
                certPriceData: null,

                certTypeLabels: ['domain validated', 'wildcard, domain validated', 'org. validated',
                    'wildcard, org. validated', 'extended validation', 'wildcard, exended validation'],

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

            cdnCerts() {
                if (!this.results || !this.results.tls || !this.results.certificates){
                    return {};
                }

                return util.cdnCerts(this.results.tls, this.results.certificates);
            },

            tlsCerts(){
                return _.filter(_.map(_.keys(this.tlsCertsIdsMap), x => {
                    return this.results.certificates[x];
                }), crt => {
                    return !this.excludeCdns || !(crt.id in this.cdnCerts);
                });
            },

            certs(){
                if (!this.results || !this.results.certificates){
                    return {};
                }

                return _.filter(this.results.certificates, x=>{
                    return ((x.id in this.tlsCertsIdsMap) || (x.valid_to_days >= -28)) &&
                            (!this.excludeCdns || !(x.id in this.cdnCerts));
                });
            },

            tlsCertIssuers(){
                return this.certIssuersGen(this.tlsCerts);
            },

            allCertIssuers(){
                return this.certIssuersGen(this.certs);
            },

            kcLicense(){
                return (10 * this.certsKcManagedCount) + 1000.;
            },

            certsCostUsed(){
                return _.isEmpty(this.certPriceData) ? 0 : _.reduce(this.certPriceData, (acc, val, key) => {
                    return acc + val[1].total_price;
                }, 0.0);
            },

            certsCostUnused(){
                return this.certsCostTotal - this.certsCostUsed;
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

            certsOpsHours(){
                return this.certsOpsCount * 0.5;
            },

            certsOpsCost(){
                return this.certsOpsHours * this.hourlyPay;
            },

            totalCostWithoutKc(){
                return this.certsCostTotal + this.certsOpsCost;
            },

            certsKcNonmanagedCost(){
                // KC-nonmanaged cert cost.
                // Computed from TLS (val[1]) scan as we can save unused CT cert price.
                return _.isEmpty(this.certPriceData) ? 0 : _.reduce(this.certPriceData, (acc, val, key) => {
                    return acc + _.reduce(val[1].num_price, (subAcc, subVal, subKey) => {
                            return subAcc + subVal[1] * !val[2].mods[subKey].kcman; // kc managed? then 0 cost
                    }, 0.0);
                }, 0.0);
            },

            certsKcManagedCount(){
                // Computed from TLS scan (val[1]).
                // KC manages only TLS present cert by default.
                return _.isEmpty(this.certPriceData) ? 0 : _.reduce(this.certPriceData, (acc, val, key) => {
                    return acc + _.reduce(val[1].num_price, (subAcc, subVal, subKey) => {
                        return subAcc + subVal[0] * !!val[2].mods[subKey].kcman;
                    }, 0);
                }, 0);
            },

            certsKcManagedOpsSaving(){
                // Computed from TLS scan (val[1]) scan as this is the actual number of
                // keychest managed certificates (not CT).
                return _.isEmpty(this.certPriceData) ? 0 : _.reduce(this.certPriceData, (acc, val, key) => {
                    return acc + _.reduce(val[1].num_price, (subAcc, subVal, subKey) => {
                        return subAcc + subVal[0] * !!val[2].mods[subKey].kcman; // kc managed? then 0 units
                    }, 0.0);
                }, 0.0) * this.hourlyPay * 0.5;
            },

            certsManualManagedOpsCost(){
                // Computed from TLS scan (val[1]) scan as this is the actual number of
                // manually managed certificates (not CT).
                return _.isEmpty(this.certPriceData) ? 0 : _.reduce(this.certPriceData, (acc, val, key) => {
                    return acc + _.reduce(val[1].num_price, (subAcc, subVal, subKey) => {
                        return subAcc + subVal[0] * !val[2].mods[subKey].kcman; // kc managed? then 0 units
                    }, 0.0);
                }, 0.0) * this.hourlyPay * 0.5;
            },

            totalCostWithKc(){
                return this.kcLicense + this.certsManualManagedOpsCost + this.certsKcNonmanagedCost;
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

            extendDateField: util.extendDateField,

            processResults() {
                const curTime = moment().valueOf() / 1000.0;
                for(const [certId, cert] of Object.entries(this.results.certificates)){
                    cert.valid_to_days = Math.round(10 * (cert.valid_to_utc - curTime) / 3600.0 / 24.0) / 10;
                    cert.valid_from_days = Math.round(10 * (curTime - cert.valid_from_utc) / 3600.0 / 24.0) / 10;
                    cert.validity_sec = cert.valid_to_utc - cert.valid_from_utc;
                }

                for(const [tlsId, tls] of Object.entries(this.results.tls)){
                    tls.cdn = Req.mostFrequent([tls.cdn_cname, tls.cdn_headers, tls.cdn_reverse]);
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
                    util.invMaxTail);
            },

            cleanResults(){
                this.results = null;
                this.loadingState = 0;
                this.$emit('onReset');
            },



        },
    }
</script>

