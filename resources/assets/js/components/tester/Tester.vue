<template>
    <div class="container-fluid spark-screen">

        <div class="row">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">Paste key</a></li>
                    <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">File upload</a></li>
                    <!--<li class=""><a href="#tab_3" data-toggle="tab" aria-expanded="false">URL</a></li>-->
                    <li class=""><a href="#tab_4" data-toggle="tab" aria-expanded="false">Send e-mail</a></li>
                    <li class=""><a href="#tab_5" data-toggle="tab" aria-expanded="false">GitHub account</a></li>
                    <li class=""><a href="#tab_6" data-toggle="tab" aria-expanded="false">PGP/GnuPG keyring</a></li>
                    <li class=""><a href="#tab_7" data-toggle="tab" aria-expanded="false">Offline test</a></li>
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
                        <check-email></check-email>
                    </div>

                    <div id="tab_5" class="tab-pane">
                        <check-github-key></check-github-key>
                    </div>

                    <div id="tab_6" class="tab-pane">
                        <check-pgp-key></check-pgp-key>
                    </div>



                    <div id="tab_7" class="tab-pane">
                        <check-offline></check-offline>
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
    import CheckEmail from './CheckEmail.vue';
    import CheckOffline from './CheckOffline.vue';

    Vue.use(VueEvents);
    Vue.use(ToggleButton);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    Vue.component('check-file-key', CheckFileKey);
    Vue.component('check-github-key', CheckGitHubKey);
    Vue.component('check-pgp-key', CheckPGPKey);
    Vue.component('check-text-key', CheckTextKey);
    Vue.component('check-email', CheckEmail);
    Vue.component('check-offline', CheckOffline);

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