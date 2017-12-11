<template>
    <div class="mgmt-add-host row">

        <sbox cssBox="box-primary" :headerCollapse="false">
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
                        <label for="svc_type">Type</label>
                        <select class="form-control" id="svc_type"
                                v-model="formData.svc_type">
                            <option value="web">Web</option>
                        </select>

                        <i v-show="errors.has('svc_type')" class="fa fa-warning"></i>
                        <span v-show="errors.has('svc_type')" class="help is-danger"
                        >{{ errors.first('svc_type') }}</span>
                    </div>

                    <div class="form-group">
                        <label for="svc_criticality">Criticality</label>
                        <select class="form-control" id="svc_criticality"
                                v-model="formData.svc_criticality">
                            <option value="0">Low</option>
                            <option value="1">Medium</option>
                            <option value="2">High</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Assurance level</label>
                        <select class="form-control" id="svc_assurance"
                                v-model="formData.svc_assurance">
                            <option value="0">Low</option>
                            <option value="1">Medium</option>
                            <option value="2">High</option>
                        </select>

                        <i v-show="errors.has('svc_assurance')" class="fa fa-warning"></i>
                        <span v-show="errors.has('svc_assurance')" class="help is-danger"
                        >{{ errors.first('svc_assurance') }}</span>
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
                formData: {
                    id: null,
                    svc_display: '',
                    svc_name: '',
                    svc_type: 'web',
                    svc_criticality: 0,
                    svc_assurance: 0,
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

            },

            back() {
                this.$router.back();
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
