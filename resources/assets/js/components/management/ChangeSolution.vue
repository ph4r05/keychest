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
                Solution is the business unit grouping all services.
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
                        <label for="sol_type">Type</label>
                        <select class="form-control" id="sol_type"
                                v-model="formData.sol_type">
                            <option value="web">Web</option>
                        </select>

                        <i v-show="errors.has('sol_type')" class="fa fa-warning"></i>
                        <span v-show="errors.has('sol_type')" class="help is-danger"
                        >{{ errors.first('sol_type') }}</span>
                    </div>

                    <div class="form-group">
                        <label for="sol_criticality">Criticality</label>
                        <select class="form-control" id="sol_criticality"
                                v-model="formData.sol_criticality">
                            <option value="0">Low</option>
                            <option value="1">Medium</option>
                            <option value="2">High</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Assurance level</label>
                        <select class="form-control" id="sol_assurance"
                                v-model="formData.sol_assurance">
                            <option value="0">Low</option>
                            <option value="1">Medium</option>
                            <option value="2">High</option>
                        </select>

                        <i v-show="errors.has('sol_assurance')" class="fa fa-warning"></i>
                        <span v-show="errors.has('sol_assurance')" class="help is-danger"
                        >{{ errors.first('sol_assurance') }}</span>
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
                    sol_display: '',
                    sol_name: '',
                    sol_type: 'web',
                    sol_criticality: 0,
                    sol_assurance: 0,
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
