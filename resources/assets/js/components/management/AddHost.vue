<template>
    <div class="mgmt-add-host row">

        <sbox cssBox="box-primary" :headerCollapse="false">
            <template slot="title">{{ editMode ? 'Edit' : 'Add' }} managed host</template>
            <template slot="widgets">
                <button type="button" class="btn btn-box-tool"
                        data-toggle="tooltip" title="Back"
                        @click="back">
                    <i class="fa fa-chevron-left" ></i>
                </button>
            </template>

            <p>
                <template v-if="editMode">Edit the managed server.</template>
                <template v-else="">Add a new managed server.</template>
            </p>

            <div class="">
                <form @submit.prevent="inputCheck()">

                    <div class="form-group">
                        <label for="host_name">Host name</label>
                        <input type="text" id="host_name" name="host_name"
                               class="form-control" placeholder="host name"
                               v-model="formData.host_name"/>
                    </div>

                    <div class="form-group">
                        <label for="host_addr">Host address</label>
                        <input type="text" id="host_addr" name="host_addr"
                               class="form-control" placeholder="server.com:22"
                               v-validate="{max: 255, required: true, host_spec: true}"
                               data-vv-as="Host address"
                               v-model="formData.host_addr"
                               :disabled="editMode"
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

                    <div class="form-group">
                        <label>Groups</label>
                        <host-groups :tags="formData.groups"></host-groups>

                        <i v-show="errors.has('host_group')" class="fa fa-warning"></i>
                        <span v-show="errors.has('host_group')" class="help is-danger"
                        >{{ errors.first('host_group') }}</span>
                    </div>

                    <transition>
                        <div class="form-group" v-if="sentState != 1">
                            <button type="submit" class="btn btn-block btn-success btn-block"
                                    :disabled="hasErrors || isRequestInProgress"
                            >{{ editMode ? 'Update' : 'Save' }} Server</button>
                        </div>

                        <div class="alert alert-info-2 alert-waiting" v-if="sentState == 1">
                            <span id="info-text">Saving...</span>
                        </div>
                    </transition>
                </form>

            </div>

            <div v-if="sentState == 2">
                <hr/>

                <div class="config-host alert alert-info-2" v-if="response && !alreadyExisted">
                    <p>Please add the following SSH key to your <span class="code-block">~/.ssh/authorized_keys</span>:</p>
                    <p class="code-block ssh-key">{{ response.ssh_key_public }}</p>
                </div>
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
    import hostSpecValidator from '../../lib/validator/hostspec';
    import HostGroups from './HostGroupsSelector';

    Vue.use(VueEvents);
    Vue.use(VueScrollTo);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    Vue.component('host-groups', HostGroups);

    export default {
        components: {

        },
        data () {
            return {
                sentState: 0,
                editMode: false,
                alreadyExisted: false,
                formData: {
                    id: null,
                    host_name: '',
                    host_addr: '',
                    agent_id: '',
                    groups: [],
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
                return this.errors.has('host_addr');
            }
        },

        methods: {
            hookup(){
                VeeValidate.Validator.extend('host_spec', hostSpecValidator);
            },

            back() {
                mgmUtil.windowBack(this.$router, this.$route);
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
                            toastr.error('Host record is already present', 'Save fail', {
                                timeOut: 2000, preventDuplicates: true
                            });

                        } else {
                            toastr.error('Host could not be saved', 'Save fail', {
                                timeOut: 2000, preventDuplicates: true
                            });
                        }
                    });
            },

            onSave() {
                return new Promise((resolve, reject) => {

                    this.sentState = 1;
                    this.formData.host_name = _.isEmpty(this.formData.host_name) ? this.formData.host_addr : this.formData.host_name;
                    const params = this.formData;
                    axios.post('/home/management/hosts/' + (this.editMode ? 'edit' : 'add'), params)
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

                        // sort groups so the host group is first
                        this.formData.groups = mgmUtil.sortHostGroups(this.response.groups);

                        setTimeout(() => {
                            this.$scrollTo('.config-host');
                        }, 100);

                        toastr.success(this.editMode ?
                            'Server updated successfully.' :
                            'Server added successfully.', 'Success');

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
.config-host .ssh-key {
    word-wrap: break-word !important;
}
</style>
