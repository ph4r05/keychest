<template>
    <div class="create-server">
    <div class="create-server-bar">
        <div class="pull-right-nope form-group">
            <button type="button" class="btn btn-sm btn-success btn-block" v-on:click.prevent="showModal()">
                Add Server
            </button>
        </div>
    </div>

    <!-- Create Item Modal -->
    <div class="modal fade" id="create-item" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" id="add-server-wrapper" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="myModalLabel">Add server</h4>
                </div>
                <div class="modal-body">

                    <form method="POST" id="new-server-form" enctype="multipart/form-data" v-on:submit.prevent="createItem">

                        <div class="form-group">
                            <label for="server-add-title">Server:</label>
                            <input type="text" name="server" id="server-add-title" class="form-control"
                                   autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                                   v-model="newItem.server" placeholder="e.g., https://enigmabridge.com:443"
                                   autofocus="autofocus"/>
                            <span v-if="formErrors['server']" class="error text-danger">@{{ formErrors['server'] }}</span>
                        </div>

                        <div class="alert alert-info scan-alert" v-show="sentState == 2">
                            <span id="info-text">Sending...</span>
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
    </div>

</template>

<script>
    import axios from 'axios';
    export default {
        data () {
            return {
                newItem: {server: ''},
                formErrors: {},
                sentState: 0,
            }
        },
        methods: {
            showModal(){
                ga('send', 'event', 'servers', 'add-modal');
                $('#create-item').modal();
                setTimeout(()=>{
                    $('#server-add-title').focus();
                }, 500);
            },
            isWildcard(value){
                const t = _.trim(value);
                if (_.isEmpty(t) || _.isNull(t)){
                    return false;
                }

                return _.startsWith(t, '*') || _.startsWith(t, '%');
            },
            checkWildcard(value){
                if (!this.isWildcard(value)) {
                    this.createItemInt(); // normal work-flow - no wildcard domain
                    return;
                }

                ga('send', 'event', 'servers', 'add-server-wildcard-entered');
                swal({
                    title: 'Did you mean Active-domain?',
                    text: "You entered domain with a wildcard, it's not supported here. Did you mean Active-Domain?",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No, continue'
                }).then(() => {
                    this.doWildcard(value);
                }, (dismiss) => {
                    this.newItem = {'server': Req.removeAllWildcards(this.newItem.server)};
                    this.createItemInt();
                });
            },
            doWildcard(value){
                this.hideModal();
                this.resetInput();
                Req.switchTab('tab_2');
                this.$events.fire('add-watcher-domain', value);
            },
            hideModal(){
                $("#create-item").modal('hide');
            },
            resetInput(){
                this.newItem = {'server':''};
            },
            createItem() {
                ga('send', 'event', 'servers', 'add-server');

                // Minor domain validation.
                if (_.isEmpty(this.newItem.server) || this.newItem.server.split('.').length <= 1) {
                    $('#add-server-wrapper').effect("shake");
                    toastr.error('Please enter correct domain.', 'Invalid input', {
                        timeOut: 2000,
                        preventDuplicates: true
                    });
                    return;
                }

                // UX: Wildcard entered
                this.checkWildcard(this.newItem.server);
            },
            createItemInt(){
                const onFail = (function(){
                    this.sentState = -1;
                    $('#add-server-wrapper').effect( "shake" );
                    toastr.error('Error while adding the server, please, try again later', 'Error');
                }).bind(this);

                const onDuplicate = (function(){
                    this.sentState = 0;
                    $('#add-server-wrapper').effect( "shake" );
                    toastr.error('This host is already being monitored.', 'Already present');
                }).bind(this);

                const onTooMany = (function(data){
                    this.sentState = 0;
                    $('#add-server-wrapper').effect( "shake" );
                    toastr.error('We are sorry but you just reached maximum number of '
                        + data['max_limit'] + ' monitored servers.', 'Too many servers');
                }).bind(this);

                const onSuccess = (function(data){
                    this.sentState = 1;
                    this.resetInput();
                    this.$emit('onServerAdded', data);
                    this.$events.fire('on-server-added', data);
                    this.hideModal();
                    toastr.success('Server Added Successfully.', 'Success', {preventDuplicates: true});
                }).bind(this);

                this.sentState = 2;
                axios.post('/home/servers/add', this.newItem)
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
                        } else {
                            console.log("Add server failed: " + e);
                            onFail();
                        }
                    });
            }
        }
    }
</script>
<style>
    .create-server-bar {
        padding-bottom: 10px;
        min-height: 50px;
    }
</style>
