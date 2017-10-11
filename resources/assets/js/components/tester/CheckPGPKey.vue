<template>
    <div>
        <div class="subdomains-wrapper">
            <h3>PGP</h3>
            <form @submit.prevent="pgpCheck()" data-vv-scope="pgp">
                <div class="form-group">
                    <p>
                        Check your PGP key by entering either <strong>email</strong> address or <strong>key ID</strong>.
                    </p>

                    <input placeholder="email / key ID" class="form-control" v-model="pgpSearch"
                           name="pgp"
                           v-validate="{max: 128, required: true, pgp: true}"
                           data-vv-as="PGP search query"
                    />

                    <i v-show="errors.has('pgp.pgp')" class="fa fa-warning"></i>
                    <span v-show="errors.has('pgp.pgp')" class="help is-danger"
                    >{{ errors.first('pgp.pgp') }}</span>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-block btn-success"
                            :disabled="errors.has('pgp.pgp') || isRequestInProgress"
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
                pgpSearch: null,

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
                VeeValidate.Validator.extend('pgp', pgpValidator);
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

            pgpCheck(){
                // TODO: submit check
                const onValid = () => {
                    return new Promise((resolve, reject) => {
                        this.onStartSending();
                        Req.bodyProgress(true);

                        this.generateUuid();
                        this.listenWebsocket();

                        axios.get('/tester/pgp', {params: {pgp: this.pgpSearch, uuid: this.uuid}})
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
                this.$validator.validateAll('pgp')
                    .then((result) => this.validCheck(result, 'Invalid PGP search query'))
                    .then((result) => onValid())
                    .then((result) => {
                        console.log(result);
                    })
                    .catch((err) => {
                        this.unlistenWebsocket();
                        console.warn(err);
                    });
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