<template>
    <div class="mgmt-add-host row">

        <div class="row" v-if="loadingState !== 0">
            <div class="alert alert-danger alert-waiting"
                 v-if="loadingState === 1">
                <span>Loading</span>
            </div>
            <div class="alert alert-danger alert-waiting"
                 v-if="loadingState === -1">
                <span>Error, please refresh or try again later.</span>
            </div>
        </div>

        <sbox cssBox="box-primary" :headerCollapse="false" v-if="loadingState === 0">
            <template slot="title">{{ editMode ? 'Edit' : 'Add' }} managed service</template>
            <template slot="widgets">
                <button type="button" class="btn btn-box-tool"
                        data-toggle="tooltip" title="Back"
                        @click="back">
                    <i class="fa fa-chevron-left" ></i>
                </button>
            </template>

            <p>
                <template v-if="editMode">Edit the managed service.</template>
                <template v-else="">Add a new managed service.</template>
            </p>

            <div class="">
                <form @submit.prevent="inputCheck()">

                    <div class="form-group">
                        <label for="svc_display">Service display name</label>
                        <input type="text" id="svc_display" name="svc_display"
                               class="form-control" placeholder="Service name"
                               v-model="formData.svc_display"/>
                    </div>

                    <div class="form-group">
                        <label for="svc_name">Service code</label>
                        <input type="text" id="svc_name" name="svc_name"
                               class="form-control" placeholder="e.g., enigmabridge.com"
                               v-validate="{max: 255, required: true}"
                               data-vv-as="Service code"
                               v-model="formData.svc_name"
                               :disabled="editMode"
                        />

                        <i v-show="errors.has('svc_name')" class="fa fa-warning"></i>
                        <span v-show="errors.has('svc_name')" class="help is-danger"
                        >{{ errors.first('svc_name') }}</span>
                    </div>

                    <div class="form-group">
                        <label for="svc_provider">Provider</label>
                        <select class="form-control" id="svc_provider"
                                v-model="formData.svc_provider">
                            <option value="apache">Apache</option>
                            <option value="nginx">nginx</option>
                        </select>

                        <i v-show="errors.has('svc_provider')" class="fa fa-warning"></i>
                        <span v-show="errors.has('svc_provider')" class="help is-danger"
                        >{{ errors.first('svc_provider') }}</span>
                    </div>

                    <div class="form-group">
                        <label for="svc_deployment">Deployment</label>
                        <select class="form-control" id="svc_deployment"
                                v-model="formData.svc_deployment">
                            <option value="ansible">ansible</option>
                            <option value="agent">agent</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Domain authorization</label>
                        <select class="form-control" id="svc_domain_auth"
                                v-model="formData.svc_domain_auth">
                            <option value="local-certbot">Local certbot</option>
                        </select>

                        <i v-show="errors.has('svc_domain_auth')" class="fa fa-warning"></i>
                        <span v-show="errors.has('svc_domain_auth')" class="help is-danger"
                        >{{ errors.first('svc_domain_auth') }}</span>
                    </div>

                    <div class="form-group">
                        <label>Host configuration</label>
                        <select class="form-control" id="svc_config"
                                v-model="formData.svc_config">
                            <option value="manual">Manual</option>
                        </select>

                        <i v-show="errors.has('svc_config')" class="fa fa-warning"></i>
                        <span v-show="errors.has('svc_config')" class="help is-danger"
                        >{{ errors.first('svc_config') }}</span>
                    </div>

                    <div class="form-group">
                        <label>Host Groups</label>
                        <host-groups
                                v-model="formData.host_groups"
                                :allowHostGroups="true"
                                name="host_groups"></host-groups>

                        <i v-show="errors.has('host_groups')" class="fa fa-warning"></i>
                        <span v-show="errors.has('host_groups')" class="help is-danger"
                        >{{ errors.first('host_groups') }}</span>
                    </div>

                    <div class="form-group">
                        <label>Solution</label>
                        <solutions
                                v-model="formData.solution"
                                :multiple="false"
                                :taggable="false"
                                name="sol_name"></solutions>

                        <i v-show="errors.has('sol_name')" class="fa fa-warning"></i>
                        <span v-show="errors.has('sol_name')" class="help is-danger"
                        >{{ errors.first('sol_name') }}</span>
                    </div>

                    <transition>
                        <div class="form-group" v-if="sentState != 1">
                            <button type="submit" class="btn btn-block btn-success btn-block"
                                    :disabled="hasErrors || isRequestInProgress"
                            >{{ editMode ? 'Update' : 'Save' }} Service</button>
                        </div>

                        <div class="alert alert-info-2 alert-waiting" v-if="sentState == 1">
                            <span id="info-text">Saving...</span>
                        </div>
                    </transition>
                </form>

            </div>

            <div v-if="sentState == 2">

            </div>

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
    import mgmUtil from './util';
    import swal from 'sweetalert2';
    import toastr from 'toastr';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VueScrollTo from 'vue-scrollto';
    import VeeValidate from 'vee-validate';
    import { mapFields } from 'vee-validate';
    import VueHostGroups from './HostGroupsVueSelect';
    import VueSolutions from './SolutionVueSelect';

    Vue.use(VueEvents);
    Vue.use(VueScrollTo);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    export default {
        components: {
            'host-groups': VueHostGroups,
            'solutions': VueSolutions,
        },

        props: {
            id: {

            },
        },

        data () {
            return {
                loadingState: 0,
                sentState: 0,
                editMode: false,
                formData: {
                    id: null,
                    svc_display: '',
                    svc_name: '',
                    svc_provider: 'nginx',
                    svc_deployment: 'agent',
                    svc_domain_auth: 'local-certbot',
                    svc_config: 'manual',
                    host_groups: [],
                    solution: null,
                },
                response: null,
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
                return this.errors.has('svc_name');
            }
        },

        methods: {
            hookup(){
                if (this.id){
                    this.editMode = true;
                    this.loadState = 1;
                    this.fetchData();
                }
            },

            back() {
                mgmUtil.windowBack(this.$router, this.$route);
            },

            fetchData(){
                axios.get('/home/management/services/' + this.id)
                    .then(res => {
                        this.loadState = 0;
                        this.response = res.data;
                        _.assign(this.formData, this.response.record);
                        this.formData.solution = _.isEmpty(this.response.record.solutions) ?
                            null : _.head(this.response.record.solutions);
                    })
                    .catch(err => {
                        this.loadState = -1;
                        const error = new Error(err);
                        error.code = err && err.response ? err.response.status : 0;
                        toastr.error('Error while loading. Please, try again later', 'Error');
                    });
            },

            async inputCheck(){
                const validResult = await this.$validator.validateAll();
                if (!validResult){
                    return;
                }

                this.onSave()
                    .then((result) => this.onResult(result))
                    .catch((err) => {
                        console.warn(err);
                        if (err && err.code === 410){
                            toastr.error('Service record is already present', 'Save fail', {
                                timeOut: 2000, preventDuplicates: true
                            });

                        } else {
                            toastr.error('Service could not be saved', 'Save fail', {
                                timeOut: 2000, preventDuplicates: true
                            });
                        }
                    });
            },

            onSave() {
                return new Promise((resolve, reject) => {

                    this.sentState = 1;
                    this.formData.svc_display = _.isEmpty(this.formData.svc_display) ?
                        this.formData.svc_name : this.formData.svc_display;

                    const params = this.formData;
                    axios.post('/home/management/services/' + (this.editMode ? 'edit' : 'add'), params)
                        .then(res => {
                            this.sentState = 2;
                            resolve(res);
                        })
                        .catch(err => {
                            this.sentState = 0;
                            const error = new Error(err);
                            error.code = err && err.response ? err.response.status : 0;
                            reject(error);
                        });
                });
            },

            onResult(res){
                return new Promise((resolve, reject) => {
                    Vue.nextTick( () => {
                        this.response = res.data;
                        toastr.success(this.editMode ?
                            'Service updated successfully.' :
                            'Service added successfully.', 'Success');

                        this.editMode = true;
                    });
                });
            },

        },

        events: {

        }
    }
</script>

<style scoped>

</style>
