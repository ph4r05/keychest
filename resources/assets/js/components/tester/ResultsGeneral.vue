<template>
    <transition name="fade">
    <div class="col-md-12" v-if="displayFlag">

        <div class="alert alert-info-2 alert-waiting scan-alert" v-if="isLoading">
            <span>Loading results, please wait...</span>
        </div>
        <div class="alert alert-danger-2 scan-alert" v-else-if="errorFlag">
            <span>Error during key processing, please try again later.</span>
        </div>

        <div v-if="results"><h2>Results</h2>

            <div class="alert alert-info-2" v-if="numKeys == 0">
                No keys detected
            </div>
            <div class="alert alert-success-2" v-else-if="allSafe">
                The {{ pluralize('key', numKeys) }} {{ pluralize('is', numKeys) }} secure
            </div>
            <div class="alert alert-danger-2" v-else="">
                We detected insecure {{ pluralize('key', numKeys) }}
            </div>

            <div v-for="(result, r_idx) in results.results" v-if="results.results">

                <h3 v-if="length(results.results) > 1">Key {{ r_idx + 1 }}</h3>

                <div v-for="(test, t_idx) in result.tests" v-if="result.tests && length(results.results) > 0">

                    <h4 v-if="length(result.tests) > 1">Test {{ t_idx + 1 }}</h4>

                    <div>
                        <table class="table">
                            <tbody>
                            <tr v-if="test.type">
                                <th>Key type</th>
                                <td>{{ keyType(test.type) }}</td>
                            </tr>
                            <tr v-if="test.kid">
                                <th>Key ID</th>
                                <td>0x{{ test.kid }}</td>
                            </tr>
                            <tr v-if="github && test.fname">
                                <th>Key ID</th>
                                <td>{{ test.fname }}</td>
                            </tr>
                            <tr v-if="test.fprint">
                                <th>Fingerprint</th>
                                <td>{{ test.fprint }}</td>
                            </tr>
                            <tr v-if="test.subject">
                                <th>Subject</th>
                                <td>{{ test.subject }}</td>
                            </tr>
                            <tr v-if="test.issuer">
                                <th>Issuer</th>
                                <td>{{ test.issuer }}</td>
                            </tr>
                            <tr v-if="test.created_at_utc">
                                <th>Created on</th>
                                <td>{{ momentu(test.created_at_utc * 1000.).format('MMM Do YYYY') }}</td>
                            </tr>
                            <tr v-if="test.not_valid_after_utc">
                                <th>Expiring on</th>
                                <td>{{ momentu(test.not_valid_after_utc * 1000.).format('MMM Do YYYY') }}</td>
                            </tr>
                            <tr v-if="test.n">
                                <th>Bit length</th>
                                <td>{{ bitLen(test.n) }}</td>
                            </tr>
                            <tr v-if="hasMarked(test)">
                                <th>Test result</th>
                                <td>{{ test.marked ? 'Vulnerable' : 'Safe' }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div v-else="">
                    We encountered an error and couldn't complete the test. Please check you used a valid format of
                    your key(s).
                </div>

            </div>

        </div>

    </div>
    </transition>
</template>
<script>
    import _ from 'lodash';
    import axios from 'axios';
    import moment from 'moment';
    import sprintf from 'sprintf-js';
    import Req from 'req';
    import ph4 from 'ph4';
    import testTools from './TesterTools';
    import pluralize from 'pluralize';

    import ToggleButton from 'vue-js-toggle-button';
    import toastr from 'toastr';

    import Vue from 'vue';
    import VueEvents from 'vue-events';

    Vue.use(VueEvents);
    Vue.use(ToggleButton);

    export default {
        props: {
            github: {
                type: Boolean,
                required: false,
                default: false,
            },
            pgp: {
                type: Boolean,
                required: false,
                default: false,
            },

        },

        data: function() {
            return {
                results: null,
                allSafe: true,

                sendingState: 0,
                resultsAvailable: 0,
                displayFlag: 0,
                errorFlag: 0,
            }
        },

        mounted() {
            this.$nextTick(() => {
                this.hookup();
            })
        },

        computed: {
            hasResults(){
                return this.results !== null;
            },
            resultsList(){
                return this.results && this.results.results ?  this.results.results : [];
            },
            numKeys(){
                let num = 0;
                for(const [rkey, result] of Object.entries(this.resultsList)){
                    num += result.tests ? _.size(result.tests) : 0;
                }
                return num;
            },
            isLoading(){
                return !this.hasResults;
            }
        },

        methods: {
            hookup(){

            },

            pluralize,
            keyType: testTools.keyType,
            bitLen: testTools.bitLen,

            length(x){
                return x ? _.size(x) : 0;
            },

            momentu(x){
                return moment.utc(x);
            },

            hasMarked(obj){
                return 'marked' in obj;
            },

            onReset(){
                this.results = null;
                this.allSafe = true;
                this.displayFlag = 1;
                this.errorFlag = 0;
            },

            onWaitingForResults(){
                this.displayFlag = 1;
            },

            onHide(){
                this.displayFlag = 0;
            },

            onError(){
                this.errorFlag = 1;
            },

            onResultsLoaded(data){
                this.results = data;
                this.allSafe = true;
                console.log(data);

                // Results processing.
                if (!this.results){
                    return;
                }

                if (!this.results.results){
                    this.results = [];
                    return;
                }

                for(const [rkey, result] of Object.entries(this.results.results)){
                    if (!result.tests){
                        result.tests = [];
                        continue;
                    }

                    for(const [tkey, test] of Object.entries(result.tests)){
                        if ('marked' in test){
                            this.allSafe &= !!!test.marked;
                        }
                    }
                }

            }

        },

        events: {

        }
    }
</script>
<style scoped>
    .h2-nomarg {
        margin: 0;
    }
</style>