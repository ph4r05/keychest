<template>
    <div class="container-fluid spark-screen">

        <div class="row">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">Text input</a></li>
                    <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">File upload</a></li>
                    <!--<li class=""><a href="#tab_3" data-toggle="tab" aria-expanded="false">URL</a></li>-->
                    <li class=""><a href="#tab_4" data-toggle="tab" aria-expanded="false">GitHub account</a></li>
                    <li class=""><a href="#tab_5" data-toggle="tab" aria-expanded="false">PGP</a></li>
                    <li class=""><a href="#tab_6" data-toggle="tab" aria-expanded="false">Send us an e-mail</a></li>
                    <!--<li class="pull-right"><a href="#" title="Refresh" class="text-muted"><i class="fa fa-refresh"></i></a></li>-->
                </ul>
                <div class="tab-content">
                    <div id="tab_1" class="tab-pane active">
                        <check-text-key></check-text-key>
                    </div>

                    <div id="tab_2" class="tab-pane">
                        <check-file-key></check-file-key>
                    </div>

                    <div id="tab_3" class="tab-pane">

                    </div>

                    <div id="tab_4" class="tab-pane">
                        <check-github-key></check-github-key>
                    </div>

                    <div id="tab_5" class="tab-pane">
                        <check-pgp-key></check-pgp-key>
                    </div>

                    <div id="tab_6" class="tab-pane">
                        <div class="subdomains-wrapper">
                            <h3>Send us an e-mail</h3>

                            <p>
                                For easy testing of <strong>PGP</strong> keys and <strong>SMIME</strong> certificates
                                you can send us a signed email to the account below, we will check it and send you report back.
                            </p>

                            <div class="alert alert-info text-center">
                                <h2 class="h2-nomarg"><a href="mailto:test@keychest.net" class="a-nounder">test@keychest.net</a></h2>
                            </div>

                            <p>
                                <small><strong>Note:</strong> for PGP email its better to attach your public key to
                                    the email, most of the emailing clients supports this.</small>
                            </p>

                        </div>
                    </div>
                </div>
            </div>
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

    import CheckFileKey from './CheckFileKey.vue';
    import CheckGitHubKey from './CheckGitHubKey.vue';
    import CheckPGPKey from './CheckPGPKey.vue';
    import CheckTextKey from './CheckTextKey.vue';

    Vue.use(VueEvents);
    Vue.use(ToggleButton);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    Vue.component('check-file-key', CheckFileKey);
    Vue.component('check-github-key', CheckGitHubKey);
    Vue.component('check-pgp-key', CheckPGPKey);
    Vue.component('check-text-key', CheckTextKey);

    VeeValidate.Validator.localize('en', {
        attributes: {
            url: 'URL'
        }
    });

    export default {
        mixins: [mixin],
        data: function() {
            return {
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


        },

        events: {

        }
    }
</script>
<style scoped>
    .h2-nomarg {
        margin: 0;
    }

    .a-nounder {
        text-decoration: none;
    }
</style>