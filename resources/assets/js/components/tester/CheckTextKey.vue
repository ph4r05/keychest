<template>
    <div>
        <div>
            <h4>Paste your key</h4>
            <form @submit.prevent="keyTextCheck()" data-vv-scope="keyText">
                <div class="form-group">
                    <p>
                        If you can copy your <strong>public key</strong> into clipboard, you can test it by pasting it
                        to the text box. Please check the public key is in one of the supported formats as listed
                        below.
                    </p>

                    <textarea rows="10"
                              placeholder="public key in text form"
                              class="form-control"
                              v-model="keyText"
                              name="keyText"
                              data-vv-as="Key"
                              v-validate="{required: true}"
                    ></textarea>

                    <i v-show="errors.has('keyText.keyText')" class="fa fa-warning"></i>
                    <span v-show="errors.has('keyText.keyText')" class="help is-danger"
                    >{{ errors.first('keyText.keyText') }}</span>

                </div>
                <div class="form-group">
                    <p>
                        Supported formats are:
                    </p>
                    <ul>
                        <li>X509 Certificate, PEM encoded;</li>
                        <li>RSA PEM encoded public key;</li>
                        <li>SSH public key;</li>
                        <li>PGP public key;</li>
                        <li>Raw RSA modulus (hex / decimal / base64 encoded); and</li>
                        <li>S/MIME PKCS7 Signature.</li>
                    </ul>

                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-block btn-primary"
                            :disabled="errors.has('keyText.keyText') || isRequestInProgress"
                    >Test key</button>
                </div>
            </form>
        </div>

        <div class="row result-row-txt">
            <results-general
                    ref="gresults"
            ></results-general>
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
    import mixin from './TesterMixin';

    import ToggleButton from 'vue-js-toggle-button';
    import VueScrollTo from 'vue-scrollto';
    import toastr from 'toastr';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VeeValidate from 'vee-validate';
    import { mapFields } from 'vee-validate';

    import ResultsGeneral from './ResultsGeneral.vue';

    Vue.use(VueEvents);
    Vue.use(ToggleButton);
    Vue.use(VueScrollTo);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    Vue.component('results-general', ResultsGeneral);

    export default {
        mixins: [mixin],
        data: function() {
            return {
                keyText: null,

                sendingState: 0,
                resultsAvailable: 0,
                resultsError: false,
            }
        },

        mounted() {
            this.$nextTick(() => {
                this.hookup();
            })
        },

        computed: {
            isRequestInProgress(){
                return this.sendingState === 1;
            },
            hasResults(){
                return this.resultsAvailable === 1;
            },
        },

        methods: {
            hookup(){

            },

            onStartSending(){
                this.sendingState = 1;
                this.resultsError = false;
                this.$refs.gresults.onReset();
            },

            onSendingFail(){
                this.sendingState = -1;
                this.$refs.gresults.onError();
            },

            onSendFinished(){
                this.sendingState = 2;
            },

            transitionHook(){

            },

            keyTextCheck(){
                this.$scrollTo('.result-row-txt');

                const onValid = () => {
                    return new Promise((resolve, reject)=> {
                        this.onStartSending();

                        this.generateUuid();
                        this.listenWebsocket();

                        axios.post('/tester/key', {key: this.keyText, uuid: this.uuid})
                            .then(res => {
                                this.onSendFinished();
                                resolve(res);
                            })
                            .catch(err => {
                                this.onSendingFail();
                                reject(new Error(err));
                            });
                    });
                };

                // Validate and submit
                this.$validator.validateAll('keyText')
                    .then((result) => this.validCheck(result, 'Invalid Key entered'))
                    .then((result) => onValid())
                    .then((result) => this.onSubmited(result))
                    .catch(err => {
                        this.$refs.gresults.onError();
                        this.abortResults();
                        console.log(err);
                    });
            },

            onSubmited(result){
                return new Promise((resolve, reject)=> {
                    try {
                        console.log(result);
                        const data = result.data;

                        this.scheduleResultsTimeout();
                        this.$refs.gresults.onWaitingForResults();

                        resolve(data);

                    } catch (e){
                        console.warn(e);
                        toastr.error('Unexpected result in result processing', 'Check failed', {
                            timeOut: 2000, preventDuplicates: true
                        });
                        reject(e);
                    }
                });
            },

            onResult(data){
                console.log(data);
                this.resultsAvailable = 1;
                this.$refs.gresults.onResultsLoaded(data);
            },

            onResultWaitTimeout(){
                this.resultsError = true;
                this.$refs.gresults.onError();
            },

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