<template>
    <div class="edit-server">

        <!-- Update Item Modal -->
        <div class="modal fade" id="update-item-sub" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" id="update-server-wrapper-sub" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">Update server</h4>
                    </div>
                    <div class="modal-body">

                        <form method="POST" id="update-server-form" enctype="multipart/form-data" v-on:submit.prevent="createItem">

                            <div class="form-group">
                                <label for="upd-server-name-sub">Server:</label>
                                <input type="text" name="server" id="upd-server-name-sub" class="form-control"
                                       v-model="serverItem.server" placeholder="e.g., enigmabridge.com"
                                       autofocus="autofocus"/>
                                <span v-if="formErrors['server']" class="error text-danger">@{{ formErrors['server'] }}</span>
                            </div>

                            <div class="form-group">
                                <input type="checkbox" id="sub-auto-add-edit" style="width: 90px;">
                                <label for="sub-auto-add-edit">&nbsp;Watch Now&trade; - automatic monitoring of new servers</label>
                            </div>

                            <div class="alert alert-info scan-alert" v-show="sentState == 2">
                                <span>Sending...</span>
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
    export default {
        data () {
            return {
                serverItem: {},
                formErrors: {},
                sentState: 0,
            }
        },
        mounted(){
            this.$nextTick(function () {
                this.hookup();
            })
        },
        methods: {
            hookup(){
                $('#sub-auto-add-edit').bootstrapSwitch('destroy');
                $('#sub-auto-add-edit').bootstrapSwitch();
                $('#sub-auto-add-edit').bootstrapSwitch('size','normal');
            },
            createItem() {
                // Minor domain validation.
                if (_.isEmpty(this.serverItem.server) || this.serverItem.server.split('.').length <= 1){
                    $('#update-server-wrapper-sub').effect( "shake" );
                    toastr.error('Please enter correct domain.', 'Invalid input', {timeOut: 2000, preventDuplicates: true});
                    return;
                }

                this.serverItem.autoFill = !!($('#sub-auto-add-edit').bootstrapSwitch('state'));
                $('#sub-auto-add-edit').bootstrapSwitch('_width');

                const onFail = (function(){
                    this.sentState = -1;
                }).bind(this);

                const onDuplicate = (function(){
                    this.sentState = 0;
                    $('#update-server-wrapper-sub').effect( "shake" );
                    toastr.error('This host is already being monitored.', 'Already present');
                }).bind(this);

                const onBlacklisted = (function(){
                    this.sentState = 0;
                    $('#update-server-wrapper-sub').effect( "shake" );
                    toastr.error('The selected Active Domain is currently restricted. ' +
                        'Get in touch at support@enigmabridge.com if it is your domain.', 'Restricted domain');
                }).bind(this);

                const onSuccess = (function(data){
                    this.sentState = 1;
                    this.serverItem = {};
                    this.$emit('onSubUpdated', data);
                    this.$events.fire('on-sub-updated', data);
                    $("#update-item-sub").modal('hide');
                    toastr.success('Server Updated Successfully.', 'Success');
                }).bind(this);

                this.sentState = 2;
                axios.post('/home/subs/update', this.serverItem)
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
            'on-edit-sub-watch'(data) {
                this.serverItem = data;
                this.serverItem.server = window.Req.buildUrl('https', data.scan_host, undefined);
                this.serverItem.autoFill = !!data.auto_fill_watches;
                $('#sub-auto-add-edit').bootstrapSwitch('destroy');
                $('#sub-auto-add-edit').bootstrapSwitch('state', this.serverItem.autoFill);
                $('#sub-auto-add-edit').bootstrapSwitch('size', 'normal');

                $("#update-item-sub").modal('show');
                setTimeout(()=>{
                    $("#upd-server-name-sub").focus();
                }, 500);
            },
        }
    }
</script>
<style>

</style>
