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
            <template slot="title">{{ editMode ? 'Edit' : 'Add' }} managed solution</template>
            <template slot="widgets">
                <button type="button" class="btn btn-box-tool"
                        data-toggle="tooltip" title="Back"
                        @click="back">
                    <i class="fa fa-chevron-left" ></i>
                </button>
            </template>

            <p>
                <template v-if="editMode">Edit the managed solution.</template>
                <template v-else="">Add a new managed solution.</template>
            </p>
            <p>
                Solution represents a realization of the service. It is a set of parameters defining the service
                implementation.
            </p>

            <div class="">
                <form @submit.prevent="inputCheck()">

                    <div class="form-group">
                        <label for="sol_display">Solution display name</label>
                        <input type="text" id="sol_display" name="sol_display"
                               class="form-control" placeholder="Solution name"
                               v-model="formData.sol_display"/>
                    </div>

                    <div class="form-group">
                        <label for="sol_name">Solution code</label>
                        <input type="text" id="sol_name" name="sol_name"
                               class="form-control" placeholder="e.g., enigmabridge.com"
                               v-validate="{max: 255, required: true}"
                               data-vv-as="Solution code"
                               v-model="formData.sol_name"
                               :disabled="editMode"
                        />

                        <i v-show="errors.has('sol_name')" class="fa fa-warning"></i>
                        <span v-show="errors.has('sol_name')" class="help is-danger"
                        >{{ errors.first('sol_name') }}</span>
                    </div>

                    <div class="form-group">
                        <label for="sol_provider">Provider</label>
                        <select class="form-control" id="sol_provider"
                                v-model="formData.sol_provider">
                            <option value="apache">Apache</option>
                            <option value="nginx">nginx</option>
                        </select>

                        <i v-show="errors.has('sol_provider')" class="fa fa-warning"></i>
                        <span v-show="errors.has('sol_provider')" class="help is-danger"
                        >{{ errors.first('sol_provider') }}</span>
                    </div>

                    <div class="form-group">
                        <label for="sol_deployment">Deployment</label>
                        <select class="form-control" id="sol_deployment"
                                v-model="formData.sol_deployment">
                            <option value="ansible">ansible</option>
                            <option value="agent">agent</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Domain authorization</label>
                        <select class="form-control" id="sol_domain_auth"
                                v-model="formData.sol_domain_auth">
                            <option value="local-certbot">Local certbot</option>
                        </select>

                        <i v-show="errors.has('sol_domain_auth')" class="fa fa-warning"></i>
                        <span v-show="errors.has('sol_domain_auth')" class="help is-danger"
                        >{{ errors.first('sol_domain_auth') }}</span>
                    </div>

                    <div class="form-group">
                        <label>Host configuration</label>
                        <select class="form-control" id="sol_config"
                                v-model="formData.sol_config">
                            <option value="manual">Manual</option>
                        </select>

                        <i v-show="errors.has('sol_config')" class="fa fa-warning"></i>
                        <span v-show="errors.has('sol_config')" class="help is-danger"
                        >{{ errors.first('sol_config') }}</span>
                    </div>

                    <div class="form-group">
                        <label>Host Groups</label>
                        <host-groups
                                :tags="formData.host_groups"
                                :allowHostGroups="true"
                                name="host_groups"></host-groups>

                        <i v-show="errors.has('host_groups')" class="fa fa-warning"></i>
                        <span v-show="errors.has('host_groups')" class="help is-danger"
                        >{{ errors.first('host_groups') }}</span>
                    </div>

                    <transition>
                        <div class="form-group" v-if="sentState != 1">
                            <button type="submit" class="btn btn-block btn-success btn-block"
                                    :disabled="hasErrors || isRequestInProgress"
                            >{{ editMode ? 'Update' : 'Save' }} Solution</button>
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

    Vue.use(VueEvents);
    Vue.use(VueScrollTo);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    //Vue.component('host-groups', VueHostGroups);

    export default {
        components: {
            'host-groups': VueHostGroups,
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
                    sol_display: '',
                    sol_name: '',
                    sol_provider: 'nginx',
                    sol_deployment: 'agent',
                    sol_domain_auth: 'local-certbot',
                    sol_config: 'manual',
                    host_groups: []
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
                return this.errors.has('sol_name');
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
                axios.get('/home/management/solutions/' + this.id)
                    .then(res => {
                        this.loadState = 0;
                        this.response = res.data;
                        _.assign(this.formData, this.response.record);
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
                            toastr.error('Solution record is already present', 'Save fail', {
                                timeOut: 2000, preventDuplicates: true
                            });

                        } else {
                            toastr.error('Solution could not be saved', 'Save fail', {
                                timeOut: 2000, preventDuplicates: true
                            });
                        }
                    });
            },

            onSave() {
                return new Promise((resolve, reject) => {

                    this.sentState = 1;
                    this.formData.sol_display = _.isEmpty(this.formData.sol_display) ?
                        this.formData.sol_name : this.formData.sol_display;

                    const params = this.formData;
                    axios.post('/home/management/solutions/' + (this.editMode ? 'edit' : 'add'), params)
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
                            'Solution updated successfully.' :
                            'Solution added successfully.', 'Success');

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
