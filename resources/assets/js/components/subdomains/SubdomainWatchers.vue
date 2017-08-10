<template>
    <div>
        <div class="alert alert-info alert-waiting scan-alert" id="search-info"
             v-if="loadingState == 0">
            <span>Loading data, please wait...</span>
        </div>

        <div v-show="loadingState != 0">
            <div class="row">
                <div class="col-md-7 ">
                    <add-sub-watch></add-sub-watch>
                </div>
                <div class="col-md-5">
                    <filter-bar
                            :globalEvt="false"
                            v-on:filter-set="onFilterSet"
                            v-on:filter-reset="onFilterReset"
                    ></filter-bar>
                </div>
            </div>

            <div class="table-responsive table-xfull" v-bind:class="{'loading' : loadingState==2}">
                <vuetable-my ref="vuetable"
                          api-url="/home/subs/get"
                          :fields="fields"
                          pagination-path=""
                          :css="css.table"
                          :sort-order="sortOrder"
                          :multi-sort="true"
                          :per-page="50"
                          :append-params="moreParams"
                          @vuetable:cell-clicked="onCellClicked"
                          @vuetable:pagination-data="onPaginationData"
                          @vuetable:loaded="onLoaded"
                          @vuetable:loading="onLoading"
                          @vuetable:checkbox-toggled="onCheckboxToggled"
                          @vuetable:checkbox-toggled-all="onCheckboxToggled"
                >
                    <template slot="actions" scope="props">
                        <div class="custom-actions">
                            <button class="btn btn-sm btn-primary"
                                    @click="editItemAction('edit-item', props.rowData, props.rowIndex)"><i class="glyphicon glyphicon-pencil"></i></button>
                            <button class="btn btn-sm btn-danger"
                                    @click="deleteItemAction('delete-item', props.rowData, props.rowIndex)"><i class="glyphicon glyphicon-trash"></i></button>
                        </div>
                    </template>
                    <template slot="autoadd" scope="props">
                        <span class="label label-success" v-if="props.rowData.auto_fill_watches">On</span>
                        <span class="label label-default" v-else="">Off</span>
                    </template>
                    <template slot="detected" scope="props">
                        <div v-if="formatResSize(props.rowData.sub_result_size) !== -1" class="text-right">
                            {{ props.rowData.sub_result_size }} </div>
                        <div v-else="" class="text-center">
                            <i class='fa fa-refresh fa-spin'></i></div>
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
                            @click="deleteChecked()">
                        <i class="glyphicon glyphicon-trash" title="Remove"></i></button>
                </div>
                <span>Selected {{numSelected}} active {{ numSelected | pluralize('domain') }} </span>
            </div>

            <div class="vuetable-pagination">
                <vuetable-pagination-info ref="paginationInfo"
                                          info-class="pagination-info"
                                          :css="css.info"
                ></vuetable-pagination-info>
                <vuetable-pagination-bootstrap ref="pagination"
                                               :css="css.pagination"
                                               @vuetable-pagination:change-page="onChangePage"
                ></vuetable-pagination-bootstrap>
            </div>
        </div>
    </div>
</template>
<script>
    import accounting from 'accounting';
    import moment from 'moment';
    import pluralize from 'pluralize';

    import Vue from 'vue';
    import VueEvents from 'vue-events';

    import Vuetable from 'vuetable-2/src/components/Vuetable';
    import VuetablePagination from 'vuetable-2/src/components/VuetablePagination';
    import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo';
    import VuetablePaginationBootstrap from '../../components/partials/VuetablePaginationBootstrap';

    import FilterBar from '../servers/FilterBar';
    import AddSubWatch from './watch/AddSubWatch.vue';

    Vue.use(VueEvents);
    Vue.component('filter-bar', FilterBar);
    Vue.component('add-sub-watch', AddSubWatch);

    export default {
        components: {
            Vuetable,
            VuetablePagination,
            VuetablePaginationInfo,
            VuetablePaginationBootstrap
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
                        name: 'scan_host',
                        sortField: 'scan_host',
                        title: 'Active domain',
                    },
                    {
                        name: '__slot:autoadd',
                        sortField: 'auto_fill_watches',
                        title: 'Watch Now',
                    },
                    {
                        name: 'created_at',
                        title: 'Created',
                        sortField: 'created_at',
                        titleClass: 'text-center',
                        dataClass: 'text-center',
                        callback: 'formatDate|DD-MM-YYYY HH:mm'
                    },
                    // {
                    //     name: 'updated_at',
                    //     title: 'Update',
                    //     sortField: 'updated_at',
                    //     titleClass: 'text-center',
                    //     dataClass: 'text-center',
                    //     callback: 'formatDate|DD-MM-YYYY HH:mm'
                    // },
                    {
                        name: 'last_scan_at',
                        title: 'Last scan',
                        sortField: 'last_scan_at',
                        titleClass: 'text-center',
                        dataClass: 'text-center',
                        callback: 'formatDate|DD-MM-YYYY HH:mm'
                    },
                    {
                        name: '__slot:detected',
                        title: 'Detected',
                        sortField: 'sub_result_size',
                    },
                    {
                        name: '__slot:actions',
                        title: 'Actions',
                        titleClass: 'text-center',
                        dataClass: 'text-center'
                    }
                ],
                css: {
                    table: {
                        tableClass: 'table table-bordered table-striped table-hover',
                        ascendingIcon: 'glyphicon glyphicon-chevron-up',
                        descendingIcon: 'glyphicon glyphicon-chevron-down'
                    },
                    pagination: {
                        wrapperClass: 'pagination pull-right',
                        activeClass: 'active',
                        disabledClass: 'disabled',
                        pageClass: 'page',
                        linkClass: 'link',
                    },
                    info: {
                        infoClass: "pull-left"
                    },
                    icons: {
                        first: 'glyphicon glyphicon-step-backward',
                        prev: 'glyphicon glyphicon-chevron-left',
                        next: 'glyphicon glyphicon-chevron-right',
                        last: 'glyphicon glyphicon-step-forward',
                    },
                },
                sortOrder: [
                    {field: 'scan_host', sortField: 'scan_host', direction: 'asc'}
                ],
                moreParams: {},
                numSelected: 0,
                totalUnfinished: []
            }
        },

        computed: {

        },

        methods: {
            allcap (value) {
                return value.toUpperCase()
            },
            formatNumber (value) {
                return accounting.formatNumber(value, 2)
            },
            formatDate (value, fmt = 'DD-MM-YYYY') {
                return (value === null) ? '' : moment.utc(value, 'YYYY-MM-DD HH:mm').local().format(fmt);
            },
            formatResSize(value){
                if (value === -1 || !_.isNumber(value)){
                    return -1;
                }

                return value;
            },
            onPaginationData (paginationData) {
                this.$refs.pagination.setPaginationData(paginationData);
                this.$refs.paginationInfo.setPaginationData(paginationData);
            },
            onChangePage (page) {
                this.$refs.vuetable.changePage(page);
            },
            onCellClicked (data, field, event) {
                this.$refs.vuetable.toggleDetailRow(data.id);
            },
            onLoading(){
                if (this.loadingState != 0){
                    this.loadingState = 2;
                }
            },
            onLoaded(){
                this.loadingState = 1;
            },
            onCheckboxToggled(){
                this.numSelected = _.size(this.$refs.vuetable.selectedTo);
            },
            invertCheckBoxes(){
                this.$refs.vuetable.invertCheckBoxes();
            },
            uncheckAll(){
                this.$refs.vuetable.uncheckAllPages();
            },

            getUnfinished(){
                let unfinished = [];
                this.$refs.vuetable.tableData.forEach(function(dataItem) {
                    if (!dataItem['sub_result_size'] || dataItem['sub_result_size'] === -1){
                        unfinished.push(dataItem);
                    }
                });

                return unfinished;
            },

            loadAllUnfinished(){
                const onFail = () => {
                    this.totalUnfinished = [];
                };

                const onSuccess = data => {
                    Vue.nextTick(() => {
                        this.totalUnfinished = data['res'];
                        this.onUnfinishedLoaded();
                    });
                };

                axios.get('/home/subs/getUnfinished')
                    .then(response => {
                        if (!response || !response.data || response.data['status'] !== 'success'){
                            onFail();
                        } else {
                            onSuccess(response.data);
                        }
                    })
                    .catch(e => {
                        onFail();
                    });
            },
            onUnfinishedLoaded(){

            },

            onDeleteServer(data){
                swal({
                    title: 'Are you sure?',
                    text: "Server will be permanently removed",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes'
                }).then(() => {
                    this.onDeleteServerConfirmed(data);
                }).catch(() => {});
            },
            deleteChecked(){
                swal({
                    title: 'Are you sure?',
                    text: pluralize('Active-domain', this.numSelected, true) + " will be permanently removed",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes'
                }).then(() => {
                    this.onDeleteServerConfirmed({'ids': this.$refs.vuetable.selectedTo}, true);
                }).catch(() => {});
            },

            onDeleteServerConfirmed(data, isMore){
                const onFail = () => {
                    this.moreParams.deleteState = -1;
                    swal('Delete error', 'Server delete failed :(', 'error');
                };

                const onSuccess = (data) => {
                    this.moreParams.deleteState = 1;
                    Vue.nextTick(() => {
                        this.$refs.vuetable.refresh();
                        if (isMore) {
                            this.$refs.vuetable.uncheckAll();
                        }
                    });
                    this.$emit('onServerDeleted', data);
                    this.$events.fire('on-server-deleted', data);
                    toastr.success('Server deleted successfully.', 'Success');
                };

                this.moreParams.deleteState = 2;
                axios.post('/home/subs/del' + (isMore ? 'More' : ''), data)
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

            renderPagination(h) {
                console.log('pagpag');
                return h(
                    'div',
                    { class: {'vuetable-pagination': true} },
                    [
                        h('vuetable-pagination-info', { ref: 'paginationInfo', props: { css: this.css.paginationInfo } }),
                        h('vuetable-pagination-bootstrap', {
                            ref: 'pagination',
                            class: { 'pull-right': true },
                            props: {
                            },
                            on: {
                                'vuetable-pagination:change-page': this.onChangePage
                            }
                        })
                    ]
                )
            },
            deleteItemAction (action, data, index) {
                this.$emit('onDeleteSubWatch', data);
                this.$events.fire('on-delete-sub-watch', data);
            },
            editItemAction (action, data, index) {
                this.$emit('onEditSubWatch', data);
                this.$events.fire('on-edit-sub-watch', data);
            },
            onFilterSet(filterText){
                this.moreParams = {
                    filter: filterText
                };
                Vue.nextTick(() => this.$refs.vuetable.refresh());
            },
            onFilterReset(){
                this.moreParams = {};
                Vue.nextTick(() => this.$refs.vuetable.refresh());
            },
            onSubChanged(data){
                this.$refs.vuetable.refresh();
            }
        },
        events: {
            'on-sub-added' (data) {
                Vue.nextTick(() => this.onSubChanged(data));
            },
            'on-sub-updated'(data) {
                Vue.nextTick(() => this.onSubChanged(data));
            },
            'on-delete-sub-watch'(data) {
                this.onDeleteServer(data);
            },
            'on-manual-refresh'(){
                Vue.nextTick(() => this.$refs.vuetable.refresh());
            }
        }
    }
</script>
<style>
    .pagination {
        margin: 0;
        float: right;
    }
    .pagination a.page {
        border: 1px solid lightgray;
        border-radius: 3px;
        padding: 5px 10px;
        margin-right: 2px;
    }
    .pagination a.page.active {
        color: white;
        background-color: #337ab7;
        border: 1px solid lightgray;
        border-radius: 3px;
        padding: 5px 10px;
        margin-right: 2px;
    }
    .pagination a.btn-nav {
        border: 1px solid lightgray;
        border-radius: 3px;
        padding: 5px 7px;
        margin-right: 2px;
    }
    .pagination a.btn-nav.disabled {
        color: lightgray;
        border: 1px solid lightgray;
        border-radius: 3px;
        padding: 5px 7px;
        margin-right: 2px;
        cursor: not-allowed;
    }
    .pagination-info {
        float: left;
    }
    i.sort-icon {
        /*padding-left: 5px;*/
        font-size: 11px;
        padding-top: 4px;
    }
    .loading .vuetable {

    }
    .vuetable-pagination{
        min-height: 40px;
    }

    .table-xfull {
        margin-left: -10px;
        margin-right: -10px;
        width: auto;
    }

    .table-xfull > .table > thead > tr > th,
    .table-xfull > .table > tbody > tr > td
    {
        padding-left: 12px;
    }

</style>
