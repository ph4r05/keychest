<template>
    <div class="crease-server">
    <div class="create-server-bar">
        <div class="pull-right">
            <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#create-item">
                Add Server
            </button>
        </div>
    </div>

    <!-- Create Item Modal -->
    <div class="modal fade" id="create-item" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title" id="myModalLabel">Add server</h4>
                </div>
                <div class="modal-body">

                    <form method="POST" enctype="multipart/form-data" v-on:submit.prevent="createItem">

                        <div class="form-group">
                            <label for="server-add-title">Server:</label>
                            <input type="text" name="server" id="server-add-title" class="form-control"
                                   v-model="newItem.server" placeholder="e.g., https://enigmabridge.com"/>
                            <span v-if="formErrors['server']" class="error text-danger">@{{ formErrors['server'] }}</span>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success">Submit</button>
                        </div>

                    </form>


                </div>
            </div>
        </div>
    </div>
    </div>

</template>

<script>
    export default {
        data () {
            return {
                filterText: '',
                newItem: {},
                formErrors: {}
            }
        },
        methods: {
            doFilter () {
                this.$events.fire('filter-set', this.filterText);
            },
            resetFilter () {
                this.filterText = '';
                this.$events.fire('filter-reset');
            },
            createItem() {

            }
        }
    }
</script>
<style>
    .create-server-bar {
        padding-top: 10px;
        padding-bottom: 10px;
        min-height: 50px;
    }
</style>
