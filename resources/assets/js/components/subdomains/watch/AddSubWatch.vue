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
                    <h4 class="modal-title" id="myModalLabel">Add server</h4>
                </div>
                <div class="modal-body">

                    <form method="POST" id="new-server-form" enctype="multipart/form-data" v-on:submit.prevent="createItem">

                        <div class="form-group" v-if="!addMore">
                            <label for="domain-add-title">Domain:</label>
                            <div class="input-group">
                                <input type="text" name="server" id="domain-add-title" class="form-control input"
                                       v-model="newItem.server" placeholder="e.g., enigmabridge.com"
                                       autofocus="autofocus"/>
                                <span class="input-group-btn">
                                    <a class="btn btn-default" @click="onMore">
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
    import axios from 'axios';
    import Vue from 'vue';
    import VueEvents from 'vue-events';

    Vue.use(VueEvents);

    export default {
        data () {
            return {
                newItem: {server: '', autoFill: true},
                formErrors: {},
                sentState: 0,
                addMore: false,
                domains: ''
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
            showModal(){
                ga('send', 'event', 'subdomains', 'add-modal');
                this.addMore = false;
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
                this.showModal();
                const newDomain = Req.removeAllWildcards(domain);
                this.newItem = {'server': newDomain};
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
                    title: 'Wildcard domain entered',
                    text: "You entered domain with a wildcard. It is not needed in the " +
                    "Active-Domain section as the KeyChest scans for all sub-domains.",
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
                    toastr.error('Error while adding the domain, please, try again later', 'Error');
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
                    toastr.success(this.addMore ?
                        'The domains has been added' :
                        'The domain name has been added.', 'Success', {preventDuplicates: true});
                };

                this.sentState = 2;
                const reqData = this.addMore ? {'data': this.domains, 'autoFill': this.newItem.autoFill} : this.newItem;
                axios.post('/home/subs/' + ((this.addMore) ? 'import' : 'add'), reqData)
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
