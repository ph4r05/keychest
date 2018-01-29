<template>
    <div @click="onClick">
        <div class="table-responsive table-wrapper" ref="tbl">
            <table class="table table-condensed table-hosts">
                <tbody>
                    <!--<tr>-->
                        <!--<th class="col-md-2">Host name</th>-->
                        <!--<td class="col-md-10">{{ rowData.host_name }}</td>-->
                    <!--</tr>-->

                </tbody>
            </table>
        </div>

    </div>
</template>

<script>
    import _ from 'lodash';
    import mgmUtil from './code/util';

    import Vue from 'vue';
    import HostGroups from './HostGroupsVueSelect';

    export default {
        components: {

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
    .table-wrapper{
        margin-top: 14px;
    }

</style>

