<template>
    <div>
        <div style=""><h3>Text input</h3>
            <form @submit.prevent="keyTextCheck()" data-vv-scope="keyText">
                <div class="form-group">
                    <p>
                        Paste your <strong>public key</strong> to the field bellow
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
                        Supported formats:
                    </p>
                    <ul>
                        <li>X509 Certificate, PEM encoded</li>
                        <li>RSA PEM encoded public key</li>
                        <li>SSH public key</li>
                        <li>PGP public key</li>
                        <li>Raw RSA modulus (hex / decimal / base64 encoded)</li>
                        <li>S/MIME PKCS7 Signature</li>
                    </ul>

                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-block btn-success"
                            :disabled="errors.has('keyText.keyText') || isRequestInProgress"
                    >Test the key</button>
                </div>
            </form>
        </div>

        <transition name="fade" v-on:after-leave="transitionHook">
            <div class="row test-results" v-show="hasResults">
                <results-general
                    ref="gresults"
                ></results-general>
            </div>
        </transition>

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
    import toastr from 'toastr';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VeeValidate from 'vee-validate';
    import { mapFields } from 'vee-validate';

    import ResultsGeneral from './ResultsGeneral.vue';

    Vue.use(VueEvents);
    Vue.use(ToggleButton);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    Vue.component('results-general', ResultsGeneral);

    export default {
        mixins: [mixin],
        data: function() {
            return {
                keyText: null,

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
            },

            onSendingFail(){
                this.sendingState = -1;
            },

            onSendFinished(){
                this.sendingState = 2;
            },

            transitionHook(){

            },

            keyTextCheck(){
                const onValid = () => {
                    return new Promise((resolve, reject)=> {
                        this.onStartSending();
                        Req.bodyProgress(true);

                        this.generateUuid();
                        this.listenWebsocket();

                        axios.post('/tester/key', {key: this.keyText, uuid: this.uuid})
                            .then(res => {
                                this.onSendFinished();
                                Req.bodyProgress(false);
                                resolve(res);
                            })
                            .catch(err => {
                                this.onSendingFail();
                                Req.bodyProgress(false);
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
                        this.unlistenWebsocket();
                        console.log(err);
                    });
            },

            onSubmited(result){
                return new Promise((resolve, reject)=> {
                    try {
                        console.log(result);
                        const data = result.data;
                        resolve(data)

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