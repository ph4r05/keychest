<template>
    <div class="create-server">
    <div class="create-server-bar">
        <div class="pull-right-nope form-group">
            <button type="button" class="btn btn-sm btn-success btn-block" v-on:click.prevent="showModal()">
                Add active domain
            </button>
        </div>
    </div>

    <!-- Create Item Modal -->
    <div class="modal fade" id="create-item-sub" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" id="add-domain-wrapper-sub" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="myModalLabel">{{ editMode ? 'Update' : 'Add' }} Active-Domain</h4>
                </div>
                <div class="modal-body">

                    <form method="POST" id="new-server-form" enctype="multipart/form-data" v-on:submit.prevent="createItem">

                        <div class="form-group" v-if="!addMore">
                            <label for="domain-add-title">Domain:</label>
                            <div :class="{'input-group': !editMode}">
                                <input type="text" name="server" id="domain-add-title" class="form-control input"
                                       v-model="newItem.server" placeholder="e.g., enigmabridge.com"
                                       autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                                       @keydown="onKeydown"
                                       autofocus="autofocus"/>
                                <span class="input-group-btn" v-if="!editMode">
                                    <a class="btn btn-default" @click.prevent="onMore">
                                        <span class="fa fa-ellipsis-h"></span>
                                    </a>
                                </span>
                            </div>
                            <span v-if="formErrors['server']" class="error text-danger">@{{ formErrors['server'] }}</span>
                        </div>
                        <div class="form-group" v-else="">
                            <label for="domain-add-title-more">Domains:</label>
                            <textarea name="server" id="domain-add-title-more" class="form-control input"
                                      v-model="domains" placeholder="e.g., enigmabridge.com one domain per line"></textarea>
                            <span v-if="formErrors['server']" class="error text-danger">@{{ formErrors['server'] }}</span>
                        </div>

                        <div class="form-group">
                            <input type="checkbox" id="sub-auto-add">
                            <label for="sub-auto-add">&nbsp;Watch Now - automatic monitoring of new servers</label>
                        </div>

                        <div class="alert alert-info" v-if="!addMore && suffixResp.length > 0">
                            Domain <i>{{ getInputDomain() }}</i> is already covered with existing
                            {{ suffixResp.length | pluralize('record') }}: <i>{{ suffixes() }}</i>
                        </div>
                        <div class="alert alert-info" v-else-if="!addMore && sldTestRes == 1">
                            This active domain will monitor all sub-domains of <i>{{ getInputDomain() }}</i>.
                             You may want to consider
                            <i>{{ currentSld }} to monitor all sub-domains of the registered domain.</i>
                        </div>

                        <div class="alert alert-info scan-alert" v-show="sentState == 2">
                            <span id="info-text">Sending...</span>
                        </div>

                        <transition name="fade">
                            <div class="alert alert-danger alert-dismissable" v-if="sentState == -1">
                                <a class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                <strong>Bugger,</strong> something broke down. Please report this issue at
                                <a href="mailto:support@enigmabridge.com?Subject=KeyChest%20error" target="_top">
                                support@enigmabridge.com</a>.
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
    </div>

</template>

<script>
    import _ from 'lodash';
    import axios from 'axios';
    import pluralize from 'pluralize';
    import Req from 'req';

    import Vue from 'vue';
    import VueEvents from 'vue-events';

    Vue.use(VueEvents);

    export default {
        data () {
            return {
                editMode: false,
                newItem: {
                    server: '',
                    autoFill: true
                },
                formErrors: {},
                sentState: 0,
                addMore: false,
                domains: '',
                
                checkState: 0,  // stage of the test
                suffixResp: [],
                currentSld: null,
                sldTestRes: 0,

                _checkValidFnc: null,
                _resolverFnc: null,
            }
        },
        mounted(){
            this.$nextTick(function () {
                this.hookup();
            })
        },
        methods: {
            hookup(){
                $('#sub-auto-add').bootstrapSwitch('destroy');
                $('#sub-auto-add').bootstrapSwitch();
                $('#sub-auto-add').bootstrapSwitch('size','normal');
                $('#sub-auto-add').bootstrapSwitch('state', true);
            },

            showModal(edit){
                ga('send', 'event', 'subdomains', edit ? 'edit-modal' : 'add-modal');
                this.resetState();
                $('#sub-auto-add').bootstrapSwitch('state', true);
                $('#create-item-sub').modal();
                this.focusInput();
            },

            isWildcard(value){
                const t = _.trim(value);
                if (_.isEmpty(t) || _.isNull(t)){
                    return false;
                }

                return _.startsWith(t, '*') || _.startsWith(t, '%');
            },

            onAddDomain(domain){
                const newDomain = Req.removeAllWildcards(domain);
                this.showModal(false);
                this.newItem = {'server': newDomain};
            },

            onEditDomain(data){
                this.showModal(true);

                _.assignIn(this.newItem, data);
                this.newItem.server = window.Req.buildUrl('https', data.scan_host, undefined);
                this.newItem.autoFill = !!data.auto_fill_watches;
                this.editMode = true;

                $('#sub-auto-add').bootstrapSwitch('state', this.newItem.autoFill);
            },

            getInput(){
                return _.trim(this.addMore ? this.domains : this.newItem.server);
            },

            getInputDomain(){
                const url = Req.parseUrl(this.getInput());
                return url ? url.host : '';
            },

            focusInput(){
                setTimeout(()=>{
                    if (!this.addMore) {
                        $('#domain-add-title').focus();
                    } else {
                        $('#domain-add-title-more').focus();
                    }
                }, this.addMore ? 100 : 500);
            },

            onMore(){
                ga('send', 'event', 'subdomains', 'add-more');
                this.domains = this.newItem.server;
                this.addMore = true;
                this.focusInput();
            },

            onKeydown(){
                if (!this._checkValidFnc){
                    this._checkValidFnc = _.debounce(this.checkIfMeaningful, 400);
                }

                this._checkValidFnc();
            },

            checkIfMeaningful(){
                if (this.addMore){
                    return;
                }

                this.checkSld();
                this.checkSuffix();
            },

            checkSuffix(){
                const input = this.getInputDomain();
                const onFail = () => {
                    this.checkState = 0;
                    this.suffixResp = [];
                };

                const onSuccess = data => {
                    this.checkState = 0;
                    this.suffixResp = _.filter(data['status'] === 'existing' ? _.castArray(data['enabled']) : [],
                        dataRes => {
                            return !this.editMode || dataRes['scan_host'] !== input;
                        });
                };

                this.checkState = 2;
                axios.get('/home/subs/suffix', {params:{host: input}})
                    .then(response => {
                        if (!response || !response.data) {
                            onFail();
                        } else {
                            onSuccess(response.data);
                        }
                    })
                    .catch(e => {
                        onFail();
                    });
            },

            suffixes(){
                return _.join(_.map(_.take(this.suffixResp, 5), x=>{
                    return x['scan_host'];
                }), ', ');
            },

            checkSld(){
                if (!this._resolverFnc){
                    this._resolverFnc = _.memoize(Psl.get);
                }

                const input = this.getInputDomain();
                this.currentSld = this._resolverFnc(input);
                this.sldTestRes = !_.isEmpty(input) && !_.isEmpty(this.currentSld) && this.currentSld !== input;
            },

            resetState(){
                this.editMode = false;
                this.sentState = 0;
                this.addMore = false;
                this.domains = '';
                this.newItem.server = '';
                this.newItem.autoFill = true;

                this.checkState = 0;  // stage of the test
                this.suffixResp = [];
                this.currentSld = null;
                this.sldTestRes = 0;
            },

            checkWildcard(){
                if (this.addMore || !this.isWildcard(this.newItem.server)){
                    this.createItemInt();
                    return;
                }

                const cont = () => {
                    this.newItem.server = Req.removeAllWildcards(this.newItem.server);
                    this.createItemInt();
                };

                ga('send', 'event', 'servers', 'add-domain-wildcard-entered');
                swal({
                    title: 'We do wildcards for you',
                    text: "Whatever Active Domain you enter, we will look for certificates in all sub-domains, " +
                     "and start monitoring them (if Watch Now is 'ON') as soon as they are issued. As such we will " +
                     "remove the '*' from your domain name but don't worry, we know what you want KeyChest to do for you.",
                    type: 'info',
                    confirmButtonText: 'OK',
                }).then(cont);
            },

            createItem() {
                ga('send', 'event', 'subdomains', this.addMore ? 'add-server' : 'add-server-more');

                // Minor domain validation.
                if ((this.addMore && _.isEmpty(this.domains)) || (
                        !this.addMore && (_.isEmpty(this.newItem.server) || this.newItem.server.split('.').length <= 1))) {
                    $('#add-domain-wrapper-sub').effect("shake");
                    toastr.error('Please enter a correct domain.', 'Invalid input', {
                        timeOut: 2000,
                        preventDuplicates: true
                    });
                    return;
                }

                // Simple wildcard check to notify user it is not needed. Not altering work flow, simple info modal.
                this.checkWildcard();
            },

            createItemInt(){
                this.newItem.autoFill = !!($('#sub-auto-add').bootstrapSwitch('state'));
                const onFail = () => {
                    this.sentState = -1;
                    $('#add-domain-wrapper-sub').effect("shake");
                    toastr.error('Error while '+(this.editMode ? 'updating' : 'adding')+' ' +
                        'the domain, please, try again later', 'Error');
                };

                const onDuplicate = () => {
                    this.sentState = 0;
                    $('#add-domain-wrapper-sub').effect("shake");
                    toastr.error('This domain name is already set.', 'Already present');
                };

                const onBlacklisted = () => {
                    this.sentState = 0;
                    $('#add-domain-wrapper-sub').effect("shake");
                    toastr.error('The selected Active Domain is currently restricted. ' +
                        'Get in touch at support@enigmabridge.com if it is your domain.', 'Restricted domain');
                };

                const onSuccess = data => {
                    this.sentState = 1;
                    this.newItem = {'server': ''};
                    this.domains = '';
                    this.$emit('onSubAdded', data);
                    this.$events.fire('on-sub-added', data);
                    $("#create-item-sub").modal('hide');
                    toastr.success('The '
                        +(this.addMore ? 'domains' : 'domain')
                        +' has been '
                        + (this.editMode ? 'updated' : 'added'),
                        'Success', {preventDuplicates: true});
                };

                this.sentState = 2;
                const addEndpoint = '/home/subs/' + ((this.addMore) ? 'import' : 'add');
                const reqData = this.addMore ? {'data': this.domains, 'autoFill': this.newItem.autoFill} : this.newItem;
                axios.post(this.editMode ? '/home/subs/update' : addEndpoint, reqData)
                    .then(response => {
                        if (!response || !response.data) {
                            onFail();
                        } else if (response.data['status'] === 'already-present'){
                            onDuplicate();
                        } else if (response.data['status'] === 'success') {
                            onSuccess(response.data);
                        } else {
                            onFail();
                        }
                    })
                    .catch(e => {
                        if (e && e.response && e.response.status === 410){
                            onDuplicate();
                        } else if (e && e.response && e.response.status === 450) {
                            onBlacklisted();
                        } else {
                            console.log("Add server failed: " + e);
                            onFail();
                        }
                    });
            }
        },
        events: {
            'add-watcher-domain' (domain) {
                Vue.nextTick(() => this.onAddDomain(domain));
            },

            'on-edit-sub-watch'(data) {
                Vue.nextTick(() => this.onEditDomain(data));
            },
        }
    }
</script>
<style>
    .create-server-bar {
        padding-bottom: 10px;
        min-height: 50px;
    }

    #domain-add-title-more {
        height: 6em;
    }
</style>
