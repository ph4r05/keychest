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
                                v-if="!isEdit('username')"
                                v-on:click="switchEdit('username')">
                            <span>Change</span>
                        </button>
                        <button type="button"
                                class="btn btn-sm btn-success btn-block"
                                v-else=""
                                v-on:click="switchEdit('username')">
                            <span>Confirm</span>
                        </button>
                    </td>
                </tr>
                <tr>
                    <th>Email address</th>
                    <td>{{ email }}</td>
                    <td>
                        <!--<button type="button" disabled="disabled" class="btn btn-sm btn-default btn-block">-->
                            <!--Verify (coming soon)-->
                        <!--</button>-->
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
                                v-if="!isEdit('notifEmail')"
                                v-on:click="switchEdit('notifEmail')">
                            Change
                        </button>
                        <button type="button"
                                class="btn btn-sm btn-success btn-block"
                                v-else=""
                                v-on:click="switchEdit('notifEmail')">
                            Confirm
                        </button>
                    </td>
                </tr>
                <tr>
                    <th>Your timezone</th>
                    <td v-if="!isEdit('tz')">{{ tz }}</td>
                    <td v-else="">
                        <v-select v-model="tz" :options="allTzs"></v-select>
                    </td>
                    <td><button type="button"
                                v-if="!isEdit('tz')"
                                class="btn btn-sm btn-default btn-block"
                                v-on:click="switchEdit('tz')">
                        Change
                    </button>
                        <button type="button"
                                v-else=""
                                class="btn btn-sm btn-success btn-block"
                                v-on:click="switchEdit('tz')">
                        Confirm
                    </button></td>
                </tr>
                <tr>
                    <th>Weekly emails for renewals due in 28 days</th>
                    <td v-if="!isEdit('weeklyEnabled')">{{ !weeklyEnabled ? "disabled" : "Monday, 8:00am" }}</td>
                    <td v-else="">
                        <toggle-button v-model="weeklyEnabled"></toggle-button>
                    </td>
                    <td>
                        <button type="button"
                                class="btn btn-sm btn-default btn-block"
                                v-if="!isEdit('weeklyEnabled')"
                                v-on:click="switchEdit('weeklyEnabled')">
                            Change
                        </button>
                        <button type="button"
                                class="btn btn-sm btn-success btn-block"
                                v-else=""
                                v-on:click="switchEdit('weeklyEnabled')">
                            Confirm
                        </button>
                    </td>
                </tr>
                <tr>
                    <th>Notifications</th>
                    <td><vue-slider ref="slider" v-model="notifTypeVal" v-bind="notifTypeData"></vue-slider></td>
                    <td>
                        <button type="button"
                                class="btn btn-sm btn-default btn-block"
                                v-if="!isEdit('notifType')"
                                v-on:click="switchEdit('notifType')">
                            Change
                        </button>
                        <button type="button"
                                class="btn btn-sm btn-success btn-block"
                                v-else=""
                                v-on:click="switchEdit('notifType')">
                            Confirm
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
                        <button type="button"
                                class="btn btn-sm btn-warning btn-block"
                                @click.prevent="onCloseAccountClick()"
                                v-if="!closedAt">
                            Close account
                        </button>
                        <template v-else="">
                            Closing on {{ closingAtStr }}
                        </template>
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
    import toastr from 'toastr';
    import swal from 'sweetalert2';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import Vue2Filters from 'vue2-filters';
    import vSelect from 'vue-select';
    import Switches from 'vue-switches';
    import VeeValidate from 'vee-validate';
    import { mapFields } from 'vee-validate';
    import ToggleButton from 'vue-js-toggle-button';
    import VueSlider from 'vue-slider-component';

    Vue.use(VueEvents);
    Vue.use(Vue2Filters);
    Vue.use(ToggleButton);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});
    Vue.component('v-select', vSelect);
    Vue.component('switches', Switches);
    Vue.component('vue-slider', VueSlider);

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
            initNotifType: {
                type: Number,
                required: false,
                default: 0
            },
            initClosedAt: {
                type: Number,
                required: false,
            },
        },
        data () {
            return {
                loadingState: 0,
                numSelected: 0,
                sentState: 0,

                username: Laravel.authUserName,
                email: this.initEmail,
                notifEmail: this.initNotifEmail ? this.initNotifEmail : this.initEmail,
                tz: this.initTz,
                weeklyEnabled: !this.initWeeklyDisabled,
                createdAt: this.initCreated * 1000,
                notifType: this.initNotifType ? this.initNotifType : 0,
                notifTypeVal: null,
                closedAt: this.initClosedAt ? this.initClosedAt * 1000 : undefined,

                changes: {},
                editMode: {
                    username: false,
                    email: false,
                    notifEmail: false,
                    tz: false,
                    weeklyEnabled: false,
                    notifType: false,
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
            closingAtStr(){
                return this.closedAt && this.closedAt > 10 ?
                    moment.utc(this.closedAt).add(1, 'month').format("MMM Do YYYY, HH:mm") :
                    '';
            },
            allTzs(){
                return moment.tz.names();
            },
            notifTypeLabels(){
                return [
                    'None',
                    'Daily',
                    'As it happens',
                ];
            },
            notifTypeData() {
                return {
                    value: [0, 3],
                    width: '80%',
                    tooltip: 'hover',
                    background: '#00a7d7', /*this could be light-blue */
                    height: 10,
                    disabled: !this.isEdit('notifType'),
                    piecewise: true,
                    piecewiseLabel: true,
                    piecewiseStyle: {
                        "background": "#d2d6de", /*this could be gray-lte */
                        "visibility": "visible",
                        "width": "18px",
                        "height": "18px"
                    },
                    piecewiseActiveStyle: {
                        "backgroundColor": "#00a7d7", /*this could be light-blue */
                    },
                    data: this.notifTypeLabels
                };
            }
        },
        mounted() {
            this.$nextTick(function () {
                this.hookup();
            })
        },
        methods: {
            hookup(){
                this.notifTypeVal = this.notifTypeLabels[this.notifType];
            },
            getFieldvalue(field){
                if (field === 'notifType'){
                    this.notifType = this.$refs.slider.getIndex();
                    return this.notifType;
                }
                return this[field];
            },
            switchEdit(field){
                if (field==='notifEmail'
                    && this.formFields.fields.notifEmail
                    && this.formFields.fields.notifEmail.failed)
                {
                    return;
                }

                const newEditValue = field in this.editMode ? !this.editMode[field] : true;
                const newValue = this.getFieldvalue(field);

                if (newEditValue){
                    // was non-edit before -> save initial value
                    this.initialValues[field] = newValue;
                    this.editMode[field] = newEditValue;

                } else {
                    if (this.initialValues[field] === newValue){
                        this.editMode[field] = newEditValue;
                        return; // nothing has changed
                    }
                    this.updateChanges(field);
                }
            },
            isEdit(field){
                return field in this.editMode ? this.editMode[field] : false;
            },
            updateChanges(field){
                const onFail = () => {
                    this.sentState = -1;
                    Vue.nextTick(() => {
                        Req.bodyProgress(false);
                        toastr.error('Could not update settings. Please, try again later', 'Update failed',
                            {timeOut: 2000, preventDuplicates: true});
                    });
                };

                const onInvalidInput = () => {
                    this.sentState = 0;
                    Vue.nextTick(() => {
                        Req.bodyProgress(false);
                        toastr.error('Could not update settings. Please check the input data', 'Update failed',
                            {timeOut: 2000, preventDuplicates: true});
                    });
                };

                const onSuccess = (data) => {
                    this.sentState = 1;
                    Vue.nextTick(() => {
                        Req.bodyProgress(false);

                        if (field) {
                            this.editMode[field] = false;
                        }
                        this.$emit('onAccountUpdated', data);
                        this.$events.fire('on-account-updated', data);
                        toastr.success('Account Updated Successfully.', 'Success');
                    });
                };

                const data = _.pick(this, field ? field : _.keys(this.initialValues));

                this.sentState = 2;
                Req.bodyProgress(true);
                axios.post('/home/account/update', data)
                    .then(response => {
                        if (!response || !response.data) {
                            onFail();
                        } else if (response.data['status'] === 'success') {
                            onSuccess(response.data);
                        } else {
                            onFail();
                        }
                    })
                    .catch(e => {
                        if (e && e.response && e.response.status === 410){
                            onInvalidInput();
                        } else {
                            console.log("Update account failed: " + e);
                            onFail();
                        }
                    });
            },
            onCloseAccountClick(){
                swal({
                    title: 'Please confirm account close',
                    text: "The account will be deleted permanently. This action cannot be undone",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Close account'
                }).then(() => {
                    this.onCloseAccountConfirm();
                }).catch(() => {});
            },
            onCloseAccountConfirm(){
                swal({
                    title: 'Please confirm account close',
                    text: "Are you really sure? This is the last chance to call it off.",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes'
                }).then(() => {
                    this.onCloseAccountConfirm2();
                }).catch(() => {});
            },
            onCloseAccountConfirm2(){
                const onFail = () => {
                    Vue.nextTick(() => {
                        Req.bodyProgress(false);
                        toastr.error('Could not close the account. Please, try again later', 'Close failed',
                            {timeOut: 2000, preventDuplicates: true});
                    });
                };

                const onSuccess = (data) => {
                    Vue.nextTick(() => {
                        Req.bodyProgress(false);
                        this.closedAt = moment().utc();
                        this.$emit('onAccountClosed', data);
                        this.$events.fire('on-account-closed', data);
                        toastr.success('Account close request submitted.', 'Success');
                    });
                };

                Req.bodyProgress(true);
                axios.post('/home/account/close', {})
                    .then(response => {
                        if (!response || !response.data) {
                            onFail();
                        } else if (response.data['status'] === 'success') {
                            onSuccess(response.data);
                        } else {
                            onFail();
                        }
                    })
                    .catch(e => {
                        if (e && e.response && e.response.status === 410){
                            onFail();
                        } else {
                            console.log("Close account failed: " + e);
                            onFail();
                        }
                    });
            }
        },
        events: {

        }
    }
</script>
<style scoped>

</style>
