<template>
    <div class="mgmt-add-host row">

        <sbox cssBox="box-success" :headerCollapse="false">
            <template slot="title">Add managed host</template>
            <template slot="widgets">
                <button type="button" class="btn btn-box-tool"
                        data-toggle="tooltip" title="Back"
                        @click="back">
                    <i class="fa fa-chevron-left" ></i>
                </button>
            </template>

            <p>
                Add a new managed server.
            </p>

            <div class="table-responsive">
                <form @submit.prevent="hostCheck()">

                    <div class="form-group">
                        <label for="host_name">Host name</label>
                        <input type="text" id="host_name" name="host_name"
                               class="form-control" placeholder="host name"
                               v-model="formData.host_name"/>
                    </div>

                    <div class="form-group">
                        <label for="host_name">Host address</label>
                        <input type="text" id="host_addr" name="host_addr"
                               class="form-control" placeholder="server.com:22"
                               v-validate="{max: 255, required: true, host_spec: true}"
                               data-vv-as="Host address"
                               v-model="formData.host_addr"
                        />

                        <i v-show="errors.has('host_addr')" class="fa fa-warning"></i>
                        <span v-show="errors.has('host_addr')" class="help is-danger"
                        >{{ errors.first('host_addr') }}</span>
                    </div>

                    <div class="form-group">
                        <label for="host_agent">Subnet location</label>
                        <select class="form-control" id="host_agent">
                            <option>Master</option>
                        </select>
                    </div>

                    <transition>
                        <div class="form-group" v-if="sentState == 0">
                            <button type="submit" class="btn btn-block btn-primary btn-block"
                                    :disabled="hasErrors || isRequestInProgress"
                            >Save Server</button>
                        </div>

                        <div class="alert alert-info-2 alert-waiting" v-if="sentState == 1">
                            <span id="info-text">Saving...</span>
                        </div>
                    </transition>
                </form>

            </div>

            <div v-if="sentState == 2">
                <hr/>

                <transition>
                    <div class="alert alert-success-2">
                        <span>Host has been saved</span>
                    </div>
                </transition>

                <div class="config-host">
                    Saved! Here Please configure SSH key:
                </div>
            </div>

            <!-- TODO: on add, show instructions for SSH keys add, host configuration -->
        </sbox>

    </div>
</template>
<script>
    import _ from 'lodash';
    import accounting from 'accounting';
    import moment from 'moment';
    import pluralize from 'pluralize';
    import axios from 'axios';
    import Req from 'req';
    import swal from 'sweetalert2';
    import toastr from 'toastr';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VueScrollTo from 'vue-scrollto';
    import VeeValidate from 'vee-validate';
    import { mapFields } from 'vee-validate';
    import hostSpecValidator from '../../lib/validator/hostspec';

    Vue.use(VueEvents);
    Vue.use(VueScrollTo);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    export default {
        components: {

        },
        data () {
            return {
                sentState: 0,
                formData: {
                    host_name: '',
                    host_addr: '',
                    agent_id: ''
                }
            }
        },

        mounted() {
            this.$nextTick(() => {
                this.hookup();
            })
        },

        computed: {
            isRequestInProgress(){
                return false;
            },
            hasErrors(){
                return this.errors.has('host_addr');
            }
        },

        methods: {
            hookup(){
                VeeValidate.Validator.extend('host_spec', hostSpecValidator);
            },

            back() {
                this.$router.back();
            },

            hostCheck(){
                this.$validator.validateAll()
                    .then((result) => this.onSave())
                    .then((result) => this.onResult())
                    .catch((err) => {
                        console.warn(err);
                        toastr.error('Host could not be saved', 'Save fail', {
                            timeOut: 2000, preventDuplicates: true
                        });
                    });
            },

            onSave() {
                return new Promise((resolve, reject) => {

                    this.sentState = 1;
                    const params = this.formData;
                    axios.post('/home/management/hosts/add', params)
                        .then(res => {
                            this.sentState = 2;
                            resolve(res);
                        })
                        .catch(err => {
                            this.sentState = 0;
                            reject(new Error(err));
                        });
                });
            },

            onResult(res){
                return new Promise((resolve, reject) => {
                    Vue.nextTick( () => {
                        setTimeout(() => {
                            console.log(res);
                            this.$scrollTo('.config-host');
                        }, 100);
                    });
                });
            }
        },

        events: {

        }
    }
</script>
<style>

</style>
