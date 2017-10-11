<template>
    <div>
        <div class="subdomains-wrapper">
            <h3>GitHub account</h3>
            <form @submit.prevent="githubCheck()" data-vv-scope="github">
                <div class="form-group">
                    <p>
                        Check your SSH keys used for your GitHub account. Enter your GitHub login name:
                    </p>

                    <input placeholder="GitHub login name" class="form-control"
                           name="githubNick"
                           v-model="githubNick"
                           data-vv-as="GitHub login"
                           v-validate="{regex: /^[a-zA-Z0-9_]+$/, max: 32, required: true}" />

                    <i v-show="errors.has('github.githubNick')" class="fa fa-warning"></i>
                    <span v-show="errors.has('github.githubNick')" class="help is-danger"
                    >{{ errors.first('github.githubNick') }}</span>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-block btn-success"
                            :disabled="errors.has('github.githubNick') || isRequestInProgress"
                    >Test the key</button>
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

    export default {
        mixins: [mixin],
        data: function() {
            return {
                githubNick: null,

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

            githubCheck(){
                // Double query: github -> post keys for analysis
                // Has internal catch logic - if there are no keys promise is rejected with false.
                const onValid = () => {
                    return new Promise((resolve, reject) => {
                        const axos = Req.apiAxios();

                        this.onStartSending();
                        Req.bodyProgress(true);

                        // Get github ssh keys first.
                        axos.get('https://api.github.com/users/' + this.githubNick + '/keys')
                            .then(response => this.githubCheckKeys(response.data))
                            .then(response => {
                                this.onSendFinished();
                                Req.bodyProgress(false);
                                resolve(response);
                            })
                            .catch(e => {
                                this.onSendingFail();
                                Req.bodyProgress(false);

                                if (!e){
                                    reject(e);
                                    return;
                                }

                                console.warn(e);
                                toastr.error('Could not find given account name', 'Check failed', {
                                    timeOut: 2000, preventDuplicates: true
                                });
                                reject(new Error(e, 1));
                            });
                    });
                };

                // Validate and submit
                this.$validator.validateAll('github')
                    .then((result) => this.validCheck(result, 'Invalid GitHub login name'))
                    .then((result) => onValid())
                    .then((result) => {
                        console.log(result);
                    })
                    .catch((err) => {
                        if (!err){
                            return;
                        }
                        console.warn(err);
                    });
            },

            githubCheckKeys(res){
                return new Promise((resolve, reject) => {
                    if (_.isEmpty(res)){
                        toastr.success('No GitHub SSH keys found for this account', 'No GitHub keys', {
                            timeOut: 2000, preventDuplicates: true
                        });
                        reject(false); // false ~ handled
                        return;
                    }

                    axios.post('/tester/key', {keys: res, keyType: 'github'})
                        .then(function (res) {
                            resolve(res);
                        })
                        .catch(function (err) {
                            reject(new Error(err));
                        });
                });
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