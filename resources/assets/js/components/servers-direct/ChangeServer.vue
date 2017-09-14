<template>
    <div class="modal fade" id="change-ip-server" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" id="add-server-wrapper" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="myModalLabel">Add server</h4>
                </div>
                <div class="modal-body">

                    <form method="POST" id="new-server-form" enctype="multipart/form-data" v-on:submit.prevent="createItem">

                        <div class="form-group" :class="{'has-error': errors.has('server') }">
                            <label for="server-add-title">Server:</label>
                            <input type="text" name="server" id="server-add-title" class="form-control"
                                   autocorrect="off" autocapitalize="off" spellcheck="false"
                                   v-model="scanRecord.server" placeholder="e.g., https://enigmabridge.com:443"
                                   v-validate data-vv-rules="required|url"/>
                            <span v-if="formErrors['server']" class="error text-danger">@{{ formErrors['server'] }}</span>
                            <i v-show="errors.has('server')" class="fa fa-warning"></i>
                            <span v-show="errors.has('server')" class="help is-danger">{{ errors.first('server') }}</span>
                        </div>

                        <div class="form-group" :class="{'has-error': errors.has('scan-range') }">
                            <label for="scan-range">Scan range:</label>
                            <input type="text" name="scan-range" id="scan-range" class="form-control"
                                   autocorrect="off" autocapitalize="off" spellcheck="false"
                                   v-model="scanRecord.scan_range" placeholder="e.g., 192.168.0.1 - 192.168.0.255"
                                   ref="scanRange" @input="sanitizeRange" @change="sanitizeRange"
                                   v-validate data-vv-rules="required|iprange:256"/>
                            <span v-if="formErrors['scan-range']" class="error text-danger">@{{ formErrors['scan-range'] }}</span>
                            <i v-show="errors.has('scan-range')" class="fa fa-warning"></i>
                            <span v-show="errors.has('scan-range')" class="help is-danger">{{ errors.first('scan-range') }}</span>
                        </div>

                        <div class="alert alert-info scan-alert" v-show="sentState == 2">
                            <span id="info-text">Saving...</span>
                        </div>

                        <transition name="fade">
                            <div class="alert alert-danger alert-dismissable" v-if="sentState == -1">
                                <a class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                <strong>Bugger,</strong> something broke down. Please try again or report this issue.
                            </div>
                        </transition>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success" v-bind:disabled="sentState == 2">Submit</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

</template>

<script>
    import _ from 'lodash';
    import axios from 'axios';
    import Req from 'req';
    import toastr from 'toastr';

    import Vue from 'vue';
    import VeeValidate from 'vee-validate';
    import ipValidator from '../../lib/validator/iprange';

    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    export default {
        data () {
            return {
                editMode: false,
                scanRecord: {
                    server: '',
                    scan_range: ''
                },
                formErrors: {},
                sentState: 0,
            }
        },
        mounted() {
            this.$nextTick(function () {
                this.hookup();
            })
        },
        methods: {
            hookup(){
                VeeValidate.Validator.extend('iprange', ipValidator);
            },
            onAdd(data){
                this.resetState();
                this.showModal(false);
            },
            onEdit(data){
                this.resetState();

                _.assignIn(this.scanRecord, data);
                this.scanRecord.server = Req.buildUrl(
                    'https',
                    data.service_name,
                    data.service_port === 443 ? null : data.service_port);

                this.scanRecord.scan_range = this.getTextRange(data.ip_beg, data.ip_end);
                this.editMode = true;

                this.showModal(false);
            },
            showModal(edit){
                ga('send', 'event', 'direct-servers', edit ? 'edit-modal' : 'add-modal');
                $('#change-ip-server').modal();
                this.focusInput();
            },
            isWildcard(value){
                const t = _.trim(value);
                if (_.isEmpty(t) || _.isNull(t)){
                    return false;
                }

                return _.startsWith(t, '*') || _.startsWith(t, '%');
            },
            resetState(){
                this.editMode = false;
                this.sentState = 0;
                this.scanRecord.server = '';
                this.scanRecord.scan_range = '';
            },
            checkWildcard(value){
                if (!this.isWildcard(value)) {
                    this.createItemInt(); // normal work-flow - no wildcard domain
                    return;
                }

                ga('send', 'event', 'direct-servers', 'add-server-wildcard-entered');
                this.scanRecord.server = Req.removeAllWildcards(this.scanRecord.server);
                this.createItemInt();
            },
            hideModal(){
                $("#change-ip-server").modal('hide');
            },
            resetInput(){
                this.scanRecord = {
                    'server':'',
                    'scan_range':''
                };
            },
            focusInput(){
                 setTimeout(()=>{
                     $('#server-add-title').focus();
                 }, 1000);
            },
            createItem() {
                ga('send', 'event', 'servers', 'add-server');

                // Minor domain validation.
                if (_.isEmpty(this.scanRecord.server) || this.scanRecord.server.split('.').length <= 1) {
                    $('#add-server-wrapper').effect("shake");
                    toastr.error('Please enter correct domain.', 'Invalid input', {
                        timeOut: 2000,
                        preventDuplicates: true
                    });
                    return;
                }

                // UX: Wildcard entered
                this.checkWildcard(this.scanRecord.server);
            },
            createItemInt(){
                const onFail = () => {
                    this.sentState = -1;
                    $('#add-server-wrapper').effect("shake");
                    toastr.error('Error while adding the server, please, try again later', 'Error');
                };

                const onDuplicate = () => {
                    this.sentState = 0;
                    $('#add-server-wrapper').effect("shake");
                    toastr.error('This host is already being monitored.', 'Already present');
                };

                const onTooMany = () => {
                    this.sentState = 0;
                    $('#add-server-wrapper').effect("shake");
                    toastr.error('We are sorry but you just reached maximum number of '
                        + data['max_limit'] + ' monitored servers.', 'Too many servers');
                };

                const onInvalidRange = () => {
                    this.sentState = 0;
                    $('#add-domain-wrapper-sub').effect("shake");
                    toastr.error('The entered IP range is invalid.', 'Invalid IP range');
                };

                const onBigRange = () => {
                    this.sentState = 0;
                    $('#add-domain-wrapper-sub').effect("shake");
                    toastr.error('The IP range is too big. ' +
                        'Get in touch at support@enigmabridge.com if you want bigger range.', 'Big IP range');
                };

                const onOverlap = () => {
                    this.sentState = 0;
                    $('#add-domain-wrapper-sub').effect("shake");
                    toastr.error('The IP range overlaps another existing range for the same server', 'IP overlap');
                };

                const onSuccess = data => {
                    this.sentState = 1;
                    this.resetInput();
                    this.$emit('onScanRecordChanged', data);
                    this.$events.fire('on-scan-record-changed', data);
                    this.hideModal();

                    toastr.success('The direct server has been '
                        + (this.editMode ? 'updated' : 'added'),
                        'Success', {preventDuplicates: true});
                };

                this.sentState = 2;
                axios.post('/home/networks/' + (this.editMode ? 'update' : 'add'), this.scanRecord)
                    .then(response => {
                        if (!response || !response.data) {
                            onFail();
                        } else if (response.data['status'] === 'already-present'){
                            onDuplicate();
                        } else if (response.data['status'] === 'too-many'){
                            onTooMany(response.data);
                        } else if (response.data['status'] === 'success') {
                            onSuccess(response.data);
                        } else {
                            onFail();
                        }
                    })
                    .catch(e => {
                        if (e && e.response && e.response.status === 410){
                            onDuplicate();
                        } else if (e && e.response && e.response.status === 429){
                            onTooMany(e.response.data);
                        } else if (e && e.response && e.response.status === 451){
                            onInvalidRange(e.response.data);
                        } else if (e && e.response && e.response.status === 452){
                            onBigRange(e.response.data);
                        }  else if (e && e.response && e.response.status === 453){
                            onOverlap(e.response.data);
                        } else {
                            console.log("Add scan record failed: " + e);
                            onFail();
                        }
                    });
            },
            getTextRange(ip_start, ip_end){
                return ip_start + ' - ' + ip_end;
            },
            sanitizeRange(input){
                const oldVal = input.target.value;
                let val = oldVal;

                val = _.trimStart(val);
                val = _.replace(val, new RegExp(/[^\s0-9.\-/]/, 'g'), '');
                if (val !== oldVal){
                    this.scanRecord.scan_range = val;
                }
            }
        },
        events: {
            'on-add-ip-server' (domain) {
                Vue.nextTick(() => this.onAdd(domain));
            },

            'on-edit-ip-server'(data) {
                Vue.nextTick(() => this.onEdit(data));
            },
        }
    }
</script>
<style>
    .create-server-bar {
        padding-bottom: 10px;
        min-height: 50px;
    }
</style>
