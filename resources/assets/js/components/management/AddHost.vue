<template>
    <div class="mgmt-add-host row">

        <sbox cssBox="box-primary" :headerCollapse="false">
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

            <div class="">
                <form @submit.prevent="hostCheck()">

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
                        <input-tags
                                placeholder="Type a group"
                                url="/home/management/groups/search"
                                :validator="tagValidator"
                                :tags="formData.groups"
                                :complexTags="true"
                                tagAccessor="group_name"
                                :tagRemovable="tagRemovable"

                        >
                            <template slot-scope="props">
                                <autocomplete
                                        v-if="!props.readOnly"
                                        v-validate="{max: 128, regex: /^([a-zA-Z0-9_/\-.]+)$/ }"
                                        name="host_group"
                                        data-vv-as="Host group"

                                        :placeholder="props.placeholder"
                                        :debounce="250"
                                        :classes="{ input: 'new-tag' }"
                                        v-model="props.t.newTag"

                                        ref="input_component"
                                        anchor="group_name"
                                        label=""
                                        url="/home/management/groups/search"

                                        :process="processGroupAutocomplete"
                                        :spaceAsTrigger="true"
                                        :customParams="{ noHostGroups: '1' }"
                                        @onEnter="props.onAdd"
                                        @onTab="props.onAdd"
                                        @on188="props.onAdd"
                                        @onRight="props.onAdd"
                                        @onSpace="props.onAdd"
                                        @onSelect="props.addNew"
                                        @onDelete="props.onDelete"
                                ></autocomplete>
                            </template>
                        </input-tags>

                        <i v-show="errors.has('host_group')" class="fa fa-warning"></i>
                        <span v-show="errors.has('host_group')" class="help is-danger"
                        >{{ errors.first('host_group') }}</span>
                    </div>

                    <transition>
                        <div class="form-group" v-if="sentState == 0">
                            <button type="submit" class="btn btn-block btn-success btn-block"
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

                <div class="config-host alert alert-info-2" v-if="response">
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
    import swal from 'sweetalert2';
    import toastr from 'toastr';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VueScrollTo from 'vue-scrollto';
    import VeeValidate from 'vee-validate';
    import { mapFields } from 'vee-validate';
    import hostSpecValidator from '../../lib/validator/hostspec';
    import InputTags from 'ph4-input-tag';
    import AutoComplete from 'ph4-autocomplete';
    import 'ph4-autocomplete/Autocomplete.css';

    Vue.use(VueEvents);
    Vue.use(VueScrollTo);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    Vue.component('input-tags', InputTags);
    Vue.component('autocomplete', AutoComplete);

    export default {
        components: {

        },
        data () {
            return {
                sentState: 0,
                formData: {
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
                this.$router.back();
            },

            async hostCheck(){
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
                    const params = this.formData;
                    axios.post('/home/management/hosts/add', params)
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
                        this.formData.groups = this.response.groups;

                        setTimeout(() => {
                            this.$scrollTo('.config-host');
                        }, 100);

                    });
                });
            },

            processGroupAutocomplete(json){
                return json.results;
            },

            tagValidator(tagValue){
                return !this.errors.has('host_group') && !_.startsWith(tagValue, 'host-');
            },

            tagRemovable(tag){
                return !_.startsWith(tag.group_name, 'host-');
            }
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

<style>
.autocomplete-list ul {
    margin-left: -5px;
    margin-top: 1px;
    margin-right: 5px;
    padding-top: 0;
    width: 100%;
    overflow-y: auto;
    max-height: 200px;
}

.autocomplete-list ul li{
    border-bottom: 1px solid #ccc;
    border-left: 1px solid #ccc;
    border-right: 1px solid #ccc;
}

.autocomplete-list ul:before{
    display: none !important;
}

.autocomplete-list .autocomplete-list-wrap {
    width: 100%;
    padding-right: 5px;
}
</style>
