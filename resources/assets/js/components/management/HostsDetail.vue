<template>
    <div @click="onClick">
        <div class="table-responsive table-wrapper" ref="tbl">
            <table class="table table-condensed table-hosts">
                <tbody>
                    <tr>
                        <th class="col-md-2">Host name</th>
                        <td class="col-md-10">{{ rowData.host_name }}</td>
                    </tr>
                    <tr>
                        <th>Host address</th>
                        <td>{{ rowData.host_addr }}:{{ rowData.ssh_port }}</td>
                    </tr>
                    <tr>
                        <th>Host groups</th>
                        <td>
                            <host-groups :tags="rowData.groups" :readOnly="true"></host-groups>
                        </td>
                    </tr>
                    <tr v-if="rowData.ssh_key">
                        <th colspan="2">SSH key</th>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="config-host alert alert-info-2" v-if="rowData.ssh_key" ref="ssh_key">
            <span class="code-block ssh-key">{{ rowData.ssh_key.pub_key }}</span>
        </div>

    </div>
</template>

<script>
    import _ from 'lodash';
    import mgmUtil from './util';

    import Vue from 'vue';
    import HostGroups from './HostGroupsVueSelect';

    export default {
        components: {
            'host-groups': HostGroups,
        },
        props: {
            rowData: {
                type: Object,
                required: true
            },
            rowIndex: {
                type: Number
            }
        },
        data(){
            return {
                clientWidth: 0,
            };
        },
        methods: {
            hookup(){
                window.addEventListener('resize', this.handleResize);
                this.handleResize();

                mgmUtil.sortHostGroupsInPlace(this.rowData.groups);
            },
            handleResize() {
                this.clientWidth = this.$refs.tbl.clientWidth;
            },
            onClick (event) {
                console.log('my-detail-row: on-click', event.target)
            },
        },
        mounted(){
            this.$nextTick(function () {
                this.hookup();
            })
        },
        beforeDestroy: function () {
            window.removeEventListener('resize', this.handleResize);
        },
    }
</script>
<style scoped>
    .config-host .ssh-key {
        word-wrap: break-word !important;
        font-size: 85%;
    }

    .config-host {
        display: grid;
    }

    .table-wrapper{
        margin-top: 14px;
    }

</style>

