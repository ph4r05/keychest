<template>
    <div>

        <div class="alert alert-info alert-waiting scan-alert" id="search-info"
             v-if="loadingState === 0">
            <span>Loading data, please wait...</span>
        </div>

        <div v-show="loadingState !== 0">
            <h3>Risk Groups <help-trigger id="mgmtSecGroupsInfoModal"/></h3>
            <!--<host-info></host-info>-->

            <div class="row">
                <div class="col-md-7">

                    <div class="create-server-bar">
                        <div class="pull-right-nope form-group">
                            <router-link :to="{name: 'addRiskGroup'}" tag="button"
                                         class="btn btn-sm btn-success btn-block"
                            >Add Risk Group</router-link>
                        </div>
                    </div>

                </div>
                <div class="col-md-5">
                    <filter-bar
                            :globalEvt="false"
                            v-on:filter-set="onFilterSet"
                            v-on:filter-reset="onFilterReset"
                    />
                </div>
            </div>

            <div class="table-responsive table-xfull" v-bind:class="{'loading' : loadingState === 2}">
                <vuetable-my ref="vuetable"
                             api-url="/home/management/sec_groups"
                             :fields="fields"
                             pagination-path=""
                             :css="css.table"
                             :sort-order="sortOrder"
                             :multi-sort="true"
                             :per-page="50"
                             :append-params="moreParams"
                             detail-row-component="mgmt-sec-group-detail-row"
                             @vuetable:pagination-data="onPaginationData"
                             @vuetable:loaded="onLoaded"
                             @vuetable:loading="onLoading"
                             @vuetable:checkbox-toggled="onCheckboxToggled"
                             @vuetable:checkbox-toggled-all="onCheckboxToggled"
                >
                    <template slot="name" slot-scope="props" @click="$parent.$emit('cell-clicked')">
                        {{ props.rowData.sgrp_name }}
                    </template>
                    <template slot="errors" slot-scope="props" @click="console.log('nahaha')">

                    </template>
                    <template slot="actions" slot-scope="props">
                        <div class="custom-actions">
                            <button class="btn btn-sm btn-primary" @click="onDetailToggle(props)"><i class="glyphicon glyphicon-info-sign"></i></button>
                            <router-link :to="{name: 'editSecGroup', params: {id :props.rowData.id}}" tag="button" class="btn btn-sm btn-primary"><i class="glyphicon glyphicon-pencil"></i></router-link>
                            <button class="btn btn-sm btn-danger" @click="onDeleteServer(props.rowData)"><i class="glyphicon glyphicon-trash"></i></button>
                        </div>
                    </template>
                </vuetable-my>
            </div>

            <div class="vuetable-bulk-actions form-group">
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-default" :class="{'disabled': numSelected==0}"
                            :disabled="numSelected==0"
                            @click="uncheckAll()">
                        <i class="fa fa-square-o" title="Deselect all servers on all pages"></i></button>
                    <button type="button" class="btn btn-sm btn-default"
                            @click="invertCheckBoxes()">
                        <i class="glyphicon glyphicon-random" title="Invert"></i></button>
                    <button type="button" class="btn btn-sm btn-danger" :class="{'disabled': numSelected==0}"
                            :disabled="numSelected==0"
                            @click="onDeleteBulk()">
                        <i class="glyphicon glyphicon-trash" title="Delete"></i></button>
                </div>
                <span>Selected {{ numSelected }} risk {{ pluralize('group', numSelected) }} </span>
                <button type="button" class="btn btn-sm pull-right btn-success" @click="downloadList" >
                    Download all risk groups
                </button>
            </div>

            <div class="vuetable-pagination form-group">
                <vuetable-pagination-info ref="paginationInfo"
                                          info-class="pagination-info"
                                          :css="css.info"
                />
                <vuetable-pagination-bootstrap ref="pagination"
                                               :css="css.pagination"
                                               @vuetable-pagination:change-page="onChangePage"
                />
            </div>

        </div>
    </div>
</template>
<script>
    import _ from 'lodash';
    import accounting from 'accounting';
    import moment from 'moment';
    import pluralize from 'pluralize';
    import axios from 'axios';
    import Req from 'req';
    import Blob from 'w3c-blob';
    import FileSaver from 'file-saver';
    import swal from 'sweetalert2';
    import toastr from 'toastr';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VueRouter from 'vue-router';
    import Vue2Filters from 'vue2-filters';

    import Vuetable from 'vuetable-2/src/components/Vuetable';
    import VuetablePagination from 'vuetable-2/src/components/VuetablePagination';
    import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo';
    import VuetablePaginationBootstrap from '../../components/partials/VuetablePaginationBootstrap';

    import util from './code/util';
    import TableMixin from './code/tableMix';
    import TableDefaultHandlersMixin from './code/tableDefaultHandlersMix';
    import './css/table.css';

    import FilterBar from '../partials/FilterBar.vue';
    import DetailRow from './SecGroupDetail.vue';

    Vue.use(VueEvents);
    Vue.use(VueRouter);
    Vue.use(Vue2Filters);

    // Global detail row registration because my-vuetable would not see locally registered detail row in this component.
    Vue.component('mgmt-sec-group-detail-row', DetailRow);

    export default {
        mixins: [
            TableMixin,
            TableDefaultHandlersMixin,
        ],

        components: {
            Vuetable,
            VuetablePagination,
            VuetablePaginationInfo,
            VuetablePaginationBootstrap,
            'filter-bar': FilterBar,
        },

        data () {
            return {
                loadingState: 0,
                fields: [
                    {
                        name: '__checkbox'
                    },
                    {
                        name: '__sequence',
                        title: '#',
                        titleClass: 'text-right',
                        dataClass: 'text-right'
                    },
                    {
                        name: '__slot:name',
                        sortField: 'sgrp_name',
                        title: 'Name',
                    },
                    {
                        name: 'created_at',
                        title: 'Created',
                        sortField: 'created_at',
                        titleClass: 'text-center',
                        dataClass: 'text-center',
                        callback: 'formatDate|DD-MM-YYYY'
                    },
                    {
                        name: '__slot:errors',
                        title: 'Errors',
                        sortField: 'dns_error',
                        titleClass: 'text-center',
                        dataClass: 'text-center',
                    },
                    {
                        name: '__slot:actions',
                        title: 'Actions',
                        titleClass: 'text-center',
                        dataClass: 'text-center'
                    }
                ],
                css: {
                    ...util.defaultTableCss(),
                },
                sortOrder: [
                    {field: 'sgrp_name', sortField: 'sgrp_name', direction: 'asc'}
                ],
                moreParams: {},
                numSelected: 0
            }
        },

        computed: {
        },

        methods: {
            allcap: util.allcap,
            formatNumber: util.formatNumber,
            formatDate: util.formatDate,
            pluralize,

            onFilterSet(filterText){
                this.moreParams = {
                    filter: filterText
                };
                Vue.nextTick(() => this.$refs.vuetable.refresh())
            },
            onFilterReset(){
                this.moreParams = {};
                Vue.nextTick(() => this.$refs.vuetable.refresh());
            },
            onLoading(){
                if (this.loadingState !== 0){
                    this.loadingState = 2;
                }
                Req.bodyProgress(true);
            },
            onLoaded(){
                this.loadingState = 1;
                Req.bodyProgress(false);
            },
            onCheckboxToggled(){
                this.numSelected = _.size(this.$refs.vuetable.selectedTo);
            },
            onEditServer(data) {
                this.$refs.editServer.onEditServer(data);
            },
            serversLoaded(response) {
                Req.bodyProgress(false);
                const data = response.data;

                // Building the CSV from the Data two-dimensional array
                // Each column is separated by "," and new line "\n" for next row
                let acc = ['group_name'];
                for (let cur of data) {
                    const dataString = _.join([cur.sgrp_name, cur.ssh_port]);
                    acc.push(dataString);
                }

                const csvContent = _.join(acc, '\n');
                const blob = new Blob([csvContent], {type: 'text/csv;charset=utf-8'});
                FileSaver.saveAs(blob, 'risk_groups.csv');
            },
            downloadList() {
                Req.bodyProgress(true);
                const params = {
                    return_all: 1,
                    sort: 'sgrp_name'
                };

                axios.get(this.$refs.vuetable.apiUrl, {params: params})
                    .then(response => {
                        this.serversLoaded(response.data);
                    })
                    .catch(e => {
                        Req.bodyProgress(false);
                        console.warn(e);
                    });
            },
            onDeleteServer(data){
                swal({
                    title: 'Please confirm removal',
                    text: 'The selected risk group will be removed.',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Remove'
                }).then(() => {
                    this.onDeleteConfirmed(data);
                }).catch(() => {});
            },
            onDeleteBulk(){
                swal({
                    title: 'Please confirm removal',
                    text: pluralize('Group', this.numSelected, true) + ' will be removed.',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Remove'
                }).then(() => {
                    this.onDeleteConfirmed({'ids': this.$refs.vuetable.selectedTo}, true);
                }).catch(() => {});
            },
            onDeleteConfirmed(data, isMore){
                const onFail = () => {
                    this.moreParams.deleteState = -1;
                    swal('Delete error', 'Risk group delete failed :(', 'error');
                };

                const onSuccess = data => {
                    this.moreParams.deleteState = 1;
                    Vue.nextTick(() => {
                        this.$refs.vuetable.refresh();
                        if (isMore) {
                            this.$refs.vuetable.uncheckAll();
                        }
                    });

                    this.$emit('onServerDeleted', data);
                    this.$events.fire('on-server-deleted', data);
                    toastr.success(isMore ?
                        'Servers deleted successfully.' :
                        'Server deleted successfully.', 'Success');
                };

                this.moreParams.deleteState = 2;
                axios.post('/home/management/sec_groups/del' + (isMore ? 'More' : ''), data)
                    .then(response => {
                        if (!response || !response.data || response.data['status'] !== 'success'){
                            onFail();
                        } else {
                            onSuccess(response.data);
                        }
                    })
                    .catch(e => {
                        console.log( "Del server failed: " + e );
                        onFail();
                    });

            },

            getSortParam(sortOrder) {
                return _.join(_.map(sortOrder, x => {
                    if (x.sortField === 'dns_error'){
                        return 'dns_error' + '|' + x.direction + ',' + 'tls_errors' + '|' + x.direction;
                    }
                    return x.sortField + '|' + x.direction;
                }), ',');
            },

        },
        events: {
            'on-server-added' (data) {
                Vue.nextTick(() => this.$refs.vuetable.refresh());
            },
            'on-server-updated'(data) {
                Vue.nextTick(() => this.$refs.vuetable.refresh());
            },
            'on-manual-refresh'(){
                Vue.nextTick(() => this.$refs.vuetable.refresh());
            }
        }
    }
</script>
<style>

</style>
