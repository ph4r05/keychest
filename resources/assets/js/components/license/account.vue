<template>
    <div>
        <div class="table-responsive">
            <table class="tgx table table-striped table-bordered">
                <thead>
                <tr>
                    <th class="tg-9hbo">Property</th>
                    <th class="tg-9hbo">Value</th>
                    <th class="tg-9hbo">Action</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <th >Display name</th>
                    <td v-if="!isEdit('username')">{{ username }}</td>
                    <td v-else="">
                        <input type="text" name="username" v-model="username" class="form-control" placeholder="user name"/>
                    </td>
                    <td>
                        <button type="button"
                                class="btn btn-sm btn-default btn-block"
                                v-on:click="switchEdit('username')">
                            Change
                        </button>
                    </td>
                </tr>
                <tr>
                    <th>Email address</th>
                    <td>{{ email }}</td>
                    <td>
                        <button type="button" disabled="disabled" class="btn btn-sm btn-default btn-block">
                            Verify (coming soon)
                        </button>
                    </td>
                </tr>
                <tr>
                    <th>Notification address</th>
                    <td v-if="!isEdit('notifEmail')">{{ notifEmail }}</td>
                    <td v-else="">
                        <input type="text" name="notifEmail" v-model="notifEmail" class="form-control"
                               placeholder="notification email"
                               v-validate data-vv-rules="required|email"
                        />
                        <i v-show="errors.has('notifEmail')" class="fa fa-warning"></i>
                        <span v-show="errors.has('notifEmail')" class="help is-danger">{{ errors.first('notifEmail') }}</span>
                    </td>
                    <td>
                        <button type="button"
                                class="btn btn-sm btn-default btn-block"
                                v-on:click="switchEdit('notifEmail')">
                            Change
                        </button>
                    </td>
                </tr>
                <tr>
                    <th>User timezone</th>
                    <td v-if="!isEdit('tz')">{{ tz }}</td>
                    <td v-else="">
                        <v-select v-model="tz" :options="allTzs"></v-select>
                    </td>
                    <td><button type="button"
                                class="btn btn-sm btn-default btn-block"
                                v-on:click="switchEdit('tz')">
                        Change
                    </button></td>
                </tr>
                <tr>
                    <th>Email weekly updates</th>
                    <td v-if="!isEdit('weeklyDisabled')">{{ !weeklyEnabled ? "disabled" : "Monday, 8:00am" }}</td>
                    <td v-else="">
                        <!--<toggle-button v-model="weeklyDisabled" theme="bootstrap" color="primary"></toggle-button>-->
                        <toggle-button v-model="weeklyEnabled"></toggle-button>
                    </td>
                    <td>
                        <button type="button"
                                class="btn btn-sm btn-default btn-block"
                                v-on:click="switchEdit('weeklyDisabled')">
                            Change
                        </button>
                    </td>
                </tr>
                <tr>
                    <th>Notifications</th>
                    <td>none</td>
                    <td>
                        <button type="button" disabled="disabled" class="btn btn-sm btn-default btn-block">
                            Change (coming soon) - all certs/suspicious/none
                        </button>
                    </td>
                </tr>
                <tr>
                    <th>Trusted CAs</th>
                    <td>All</td>
                    <td>
                        <button type="button" disabled="disabled" class="btn btn-sm btn-default btn-block">
                            Change (coming soon)
                        </button>
                    </td>
                </tr>
                <tr>
                    <th>Created</th>
                    <td>{{ createdAtStr }} GMT</td>
                    <td>
                        <button type="button" disabled="disabled" class="btn btn-sm btn-warning btn-block">
                            Close account (coming soon)
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

    </div>
</template>
<script>
    import _ from 'lodash';
    import accounting from 'accounting';
    import moment from 'moment';
    import 'moment-timezone';
    import pluralize from 'pluralize';
    import axios from 'axios';
    import Req from 'req';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import Vue2Filters from 'vue2-filters';
    import vSelect from 'vue-select';
    import Switches from 'vue-switches';
    import VeeValidate from 'vee-validate';
    import { mapFields } from 'vee-validate';
    import ToggleButton from 'vue-js-toggle-button'

    Vue.use(VueEvents);
    Vue.use(Vue2Filters);
    Vue.use(ToggleButton);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});
    Vue.component('v-select', vSelect);
    Vue.component('switches', Switches);

    export default {
        components: {

        },
        props: {
            initEmail: {
                type: String,
                required: true
            },
            initNotifEmail: {
                type: String,
                required: true
            },
            initTz: {
                type: String,
                required: true
            },
            initWeeklyDisabled: {
                type: Number,
                required: true
            },
            initCreated: {
                type: Number,
                required: true
            },
        },
        data () {
            return {
                loadingState: 0,
                numSelected: 0,

                username: Laravel.authUserName,
                email: this.initEmail,
                notifEmail: this.initNotifEmail ? this.initNotifEmail : this.initEmail,
                tz: this.initTz,
                weeklyEnabled: !this.initWeeklyDisabled,
                createdAt: this.initCreated * 1000,

                changes: {},
                editMode: {
                    username: false,
                    email: false,
                    notifEmail: false,
                    tz: false,
                    weeklyDisabled: false
                },
                initialValues: {

                },

                Laravel: window.Laravel,
            }
        },

        computed: {
            createdAtStr(){
                return moment.utc(this.createdAt).format("MMM Do YYYY, HH:mm");
            },
            allTzs(){
                return moment.tz.names();
            },
        },
        mounted() {
            this.$nextTick(function () {
                this.hookup();
            })
        },
        methods: {
            hookup(){

            },
            switchEdit(field){
                if (field==='notifEmail'
                    && this.formFields.fields.notifEmail
                    && this.formFields.fields.notifEmail.failed)
                {
                    return;
                }
                this.editMode[field] = field in this.editMode ? !this.editMode[field] : true;
            },
            isEdit(field){
                return field in this.editMode ? this.editMode[field] : false;
            }
        },
        events: {

        }
    }
</script>
<style scoped>

</style>
