<template>
    <div class="col-md-12">
        <div style="" v-if="results"><h2>Results</h2>

            <div class="alert alert-success" v-if="allSafe">
                No vulnerable key detected
            </div>
            <div class="alert alert-danger" v-else="">
                Vulnerable key detected
            </div>

            <div v-for="(result, r_idx) in results.results" v-if="results.results">

                <h3 v-if="length(results.results) > 1">Key {{ r_idx + 1 }}</h3>

                <div v-for="(test, t_idx) in result.tests" v-if="result.tests && length(results.results) > 0">

                    <h4 v-if="length(result.tests) > 1">Test {{ t_idx + 1 }}</h4>

                    <div>
                        <table class="table">
                            <tbody>
                            <tr v-if="test.type">
                                <th>Key Type</th>
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
                                <th>Created at</th>
                                <td>{{ momentu(test.created_at_utc * 1000.).format('MMM Do YYYY') }}</td>
                            </tr>
                            <tr v-if="test.not_valid_after_utc">
                                <th>Valid until</th>
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
                    Test could not be completed
                </div>

            </div>

        </div>

    </div>
</template>
<script>
    import _ from 'lodash';
    import axios from 'axios';
    import moment from 'moment';
    import sprintf from 'sprintf-js';
    import Req from 'req';
    import ph4 from 'ph4';
    import testTools from './TesterTools';

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
        },

        methods: {
            hookup(){

            },

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