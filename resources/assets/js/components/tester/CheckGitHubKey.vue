<template>
    <div>
        <div class="subdomains-wrapper">
            <h4>GitHub account</h4>
            <form @submit.prevent="githubCheck()" data-vv-scope="github">
                <div class="form-group">
                    <p>
                        If you use RSA keys to login to your GitHub account, simply enter your GitHub login name and
                        we take care of the rest using your public GitHub data.
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
                    <button type="submit" class="btn btn-block btn-primary"
                            :disabled="errors.has('github.githubNick') || isRequestInProgress"
                    >Test key</button>
                </div>
            </form>
        </div>

        <transition name="fade" v-on:after-leave="transitionHook">
            <div class="row test-results" v-show="hasResults">
                <results-general
                        ref="gresults"
                        :github="true"
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
                githubNick: null,

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
                this.$refs.gresults.onReset();
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
                    .then((result) => this.onSubmited(result))
                    .catch((err) => {
                        if (!err){
                            return;
                        }
                        this.unlistenWebsocket();
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

                    this.generateUuid();
                    this.listenWebsocket();

                    axios.post('/tester/key', {keys: res, keyType: 'github', uuid: this.uuid})
                        .then(res => {
                            resolve(res);
                        })
                        .catch(err => {
                            reject(new Error(err));
                        });
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