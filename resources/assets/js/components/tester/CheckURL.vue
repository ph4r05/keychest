<template>
    <div>
        <div class="server-import">
            <h4>TLS server</h4>
            <form @submit.prevent="urlCheck()" data-vv-scope="url">
                <div class="form-group">
                    <p>
                        You can check your HTTPS certificate by entering your server address below:
                    </p>

                    <input placeholder="URL to check" class="form-control" v-model="url"/>

                    <i v-show="errors.has('url.url')" class="fa fa-warning"></i>
                    <span v-show="errors.has('url.url')" class="help is-danger"
                    >{{ errors.first('url.url') }}</span>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-block btn-primary"
                            :disabled="errors.has('url.url') || isRequestInProgress"
                    >Test key</button>
                </div>
            </form>
        </div>

        <transition name="fade" v-on:after-leave="transitionHook">
            <div class="row test-results" v-show="hasResults">
                <div class="xcol-md-12">
                    <sbox cssBox="box-primary">
                        <template slot="title">Results</template>

                    </sbox>
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
    import Req from 'req';
    import ph4 from 'ph4';
    import mixin from './TesterMixin';

    import ToggleButton from 'vue-js-toggle-button';
    import toastr from 'toastr';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VeeValidate from 'vee-validate';
    import { mapFields } from 'vee-validate';
    import pgpValidator from '../../lib/validator/pgp';

    Vue.use(VueEvents);
    Vue.use(ToggleButton);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    VeeValidate.Validator.localize('en', {
        attributes: {
            url: 'URL'
        }
    });

    export default {
        mixins: [mixin],
        data: function() {
            return {
                url: null,

                sendingState: 0,
                resultsAvailable: 0,
                uuid: null,
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

            onFileChange(e){
                const files = e.target.files || e.dataTransfer.files;
                if (!files.length){
                    return;
                }

                this.keyFile = files[0];
            },

            keyFileCheck(){
                const data = new FormData();
                data.append('file', this.keyFile);

                const config = {
                    onUploadProgress: (progressEvent) => {
                        const percentCompleted = Math.round( (progressEvent.loaded * 100) / progressEvent.total );
                        console.log(percentCompleted);
                    }
                };

                const onValid = () => {
                    return new Promise((resolve, reject) => {
                        this.onStartSending();
                        Req.bodyProgress(true);

                        axios.post('/tester/file', data, config)
                            .then(res => {
                                this.onSendFinished();
                                Req.bodyProgress(false);
                                resolve(res);
                            })
                            .catch(err => {
                                this.onSendFinished();
                                Req.bodyProgress(false);
                                reject(new Error(err));
                            });
                    });
                };

                // Validate and submit
                this.$validator.validateAll('keyFile')
                    .then((result) => this.validCheck(result, 'Invalid Key File'))
                    .then((result) => onValid())
                    .then((result) => {
                        console.log(result);
                        // TODO: parse uuid
                        // TODO: subscribe to uuid ws.channel
                        // TODO: set timeout 20-30 seconds for uuid ws.channel, show error after expiration.
                    })
                    .catch((err) => {
                        console.log(err);
                    })
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