<template>
    <div @click="onClick">
        <div class="table-responsive table-wrapper" ref="tbl">
            <table class="table table-condensed table-hosts">
                <tbody>
                    <tr>
                        <th class="col-md-2">Service name</th>
                        <td class="col-md-10">{{ rowData.svc_name }}</td>
                    </tr>
                    <tr>
                        <th class="col-md-2">Provider</th>
                        <td class="col-md-10">{{ rowData.svc_provider }}</td>
                    </tr>
                    <tr>
                        <th class="col-md-2">Deployment</th>
                        <td class="col-md-10">{{ rowData.svc_deployment }}</td>
                    </tr>
                    <tr>
                        <th class="col-md-2">Domain authorization</th>
                        <td class="col-md-10">{{ rowData.svc_domain_auth }}</td>
                    </tr>
                    <tr>
                        <th class="col-md-2">Host configuration</th>
                        <td class="col-md-10">{{ rowData.svc_config }}</td>
                    </tr>
                    <tr>
                        <th>Host groups</th>
                        <td>
                            <host-groups v-model="rowData.host_groups" :readOnly="true"></host-groups>
                        </td>
                    </tr>
                    <tr>
                        <th>Solution</th>
                        <td>
                            <solutions v-model="solution" :readOnly="true"></solutions>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    import _ from 'lodash';
    import mgmUtil from './util';

    import Vue from 'vue';
    import HostGroups from './HostGroupsVueSelect';
    import VueSolutions from './SolutionVueSelect';

    export default {
        components: {
            'host-groups': HostGroups,
            'solutions': VueSolutions,
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

            };
        },
        computed: {
            solution(){
                return this.rowData && this.rowData.solutions && !_.isEmpty(this.rowData.solutions) ?
                    _.head(this.rowData.solutions) : null;
            },
        },
        methods: {
            hookup(){

            },
            handleResize() {

            },
            onClick (event) {

            },
        },
        mounted(){
            this.$nextTick(function () {
                this.hookup();
            })
        },
    }
</script>
<style scoped>

    .table-wrapper{
        margin-top: 14px;
    }

</style>

