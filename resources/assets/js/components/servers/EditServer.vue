<template>
    <div class="edit-server">

        <!-- Update Item Modal -->
        <div class="modal fade" id="update-item" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" id="update-server-wrapper" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">Update server</h4>
                    </div>
                    <div class="modal-body">

                        <form method="POST" id="update-server-form" enctype="multipart/form-data" v-on:submit.prevent="createItem">

                            <div class="form-group">
                                <label for="upd-server-add-title">Server:</label>
                                <input type="text" name="server" id="upd-server-add-title" class="form-control"
                                       v-model="serverItem.server" placeholder="e.g., https://enigmabridge.com"/>
                                <span v-if="formErrors['server']" class="error text-danger">@{{ formErrors['server'] }}</span>
                            </div>

                            <div class="alert alert-info scan-alert" v-show="sentState == 2">
                                <span>Sending...</span>
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
                serverItem: {},
                formErrors: {},
                sentState: 0,
            }
        },
        methods: {
            createItem() {
                // Minor domain validation.
                if (_.isEmpty(this.serverItem.server) || this.serverItem.server.split('.').length <= 1){
                    $('#update-server-wrapper').effect( "shake" );
                    toastr.error('Please enter correct domain.', 'Invalid input', {timeOut: 2000, preventDuplicates: true});
                    return;
                }

                const onFail = (function(){
                    this.sentState = -1;
                }).bind(this);

                const onDuplicate = (function(){
                    this.sentState = 0;
                    $('#add-server-wrapper').effect( "shake" );
                    toastr.error('This host is already being monitored.', 'Already present');
                }).bind(this);

                const onSuccess = (function(data){
                    this.sentState = 1;
                    this.serverItem = {};
                    this.$emit('onServerUpdated', data);
                    this.$events.fire('on-server-updated', data);
                    $("#update-item").modal('hide');
                    toastr.success('Server Updated Successfully.', 'Success');
                }).bind(this);

                this.sentState = 2;
                axios.post('/home/servers/update', this.serverItem)
                    .then(response => {
                        if (!response || !response.data){
                            onFail();
                        } else if (response.data['status'] === 'success') {
                            onSuccess(response.data);
                        } else if (response.data['status'] === 'success') {
                            onDuplicate();
                        } else {
                            onFail();
                        }
                    })
                    .catch(e => {
                        if (e && e.response && e.response.status === 410){
                            onDuplicate();
                        } else {
                            console.log("Add server failed: " + e);
                            onFail();
                        }
                    });
            }
        },
        events: {
            'on-edit-server'(data) {
                this.serverItem = data;
                this.serverItem.server = window.Req.buildUrl(data.scan_scheme, data.scan_host, data.scan_port);
                $("#update-item").modal('show');
            },
        }
    }
</script>
<style>

</style>
