<template>
    <div class="mgmt-change-risk-group row">

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
            <template slot="title">{{ editMode ? 'Edit' : 'Add' }} risk group</template>
            <template slot="widgets">
                <button type="button" class="btn btn-box-tool"
                        data-toggle="tooltip" title="Back"
                        @click="back">
                    <i class="fa fa-chevron-left" ></i>
                </button>
            </template>

            <p>
                <template v-if="editMode">Edit the risk group.</template>
                <template v-else="">Add a new risk group.</template>
            </p>
            <p>
                Risk group groups services with the same business criticality level.
            </p>

            <div class="">
                <form @submit.prevent="inputCheck()">

                    <div class="form-group">
                        <label for="grp_display">Display name</label>
                        <input type="text" id="grp_display" name="grp_display"
                               class="form-control" placeholder="Risk group name"
                               v-model="formData.grp_display"/>
                    </div>

                    <div class="form-group">
                        <label for="grp_name">Unique code</label>
                        <input type="text" id="grp_name" name="grp_name"
                               class="form-control" placeholder="e.g., api"
                               v-validate="{max: 255, required: true}"
                               data-vv-as="Risk group code"
                               v-model="formData.grp_name"
                               :disabled="editMode"
                        />

                        <i v-show="errors.has('grp_name')" class="fa fa-warning"></i>
                        <span v-show="errors.has('grp_name')" class="help is-danger"
                        >{{ errors.first('grp_name') }}</span>
                    </div>

                    <div class="form-group">
                        <label for="grp_criticality">Criticality</label>
                        <select class="form-control" id="grp_criticality"
                                v-model="formData.grp_criticality">
                            <option value="0">Low</option>
                            <option value="1">Medium</option>
                            <option value="2">High</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Assurance level</label>
                        <select class="form-control" id="grp_assurance"
                                v-model="formData.grp_assurance">
                            <option value="0">Low</option>
                            <option value="1">Medium</option>
                            <option value="2">High</option>
                        </select>

                        <i v-show="errors.has('grp_assurance')" class="fa fa-warning"></i>
                        <span v-show="errors.has('grp_assurance')" class="help is-danger"
                        >{{ errors.first('grp_assurance') }}</span>
                    </div>

                    <transition>
                        <div class="form-group" v-if="sentState !== 1">
                            <button type="submit" class="btn btn-block btn-success btn-block"
                                    :disabled="hasErrors || isRequestInProgress"
                            >{{ editMode ? 'Update' : 'Save' }} Risk group</button>
                        </div>

                        <div class="alert alert-info-2 alert-waiting" v-if="sentState === 1">
                            <span id="info-text">Saving...</span>
                        </div>
                    </transition>
                </form>

            </div>

            <div v-if="sentState === 2">

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
    import mgmUtil from './code/util';
    import swal from 'sweetalert2';
    import toastr from 'toastr';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VueScrollTo from 'vue-scrollto';
    import VeeValidate from 'vee-validate';
    import { mapFields } from 'vee-validate';

    Vue.use(VueEvents);
    Vue.use(VueScrollTo);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    export default {
        components: {

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
                    grp_display: '',
                    grp_name: '',
                    grp_criticality: 0,
                    grp_assurance: 0,
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
                return this.errors.has('grp_name');
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
                axios.get('/home/management/sec_groups/' + this.id)
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
                            toastr.error('Risk group record is already present', 'Save fail', {
                                timeOut: 2000, preventDuplicates: true
                            });

                        } else {
                            toastr.error('Risk group could not be saved', 'Save fail', {
                                timeOut: 2000, preventDuplicates: true
                            });
                        }
                    });
            },

            onSave() {
                return new Promise((resolve, reject) => {

                    this.sentState = 1;
                    this.formData.grp_display = _.isEmpty(this.formData.grp_display) ?
                        this.formData.grp_name : this.formData.grp_display;

                    const params = this.formData;
                    axios.post('/home/management/sec_groups/' + (this.editMode ? 'edit' : 'add'), params)
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
                            'Risk group updated successfully.' :
                            'Risk group added successfully.', 'Success');

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
