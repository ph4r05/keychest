<template>
    <div>
        <div>
            <div class="row">
                <div class="col-md-5 col-md-offset-7">
                    <filter-bar
                            :globalEvt="false"
                            v-on:filter-set="onFilterSet"
                            v-on:filter-reset="onFilterReset"
                    ></filter-bar>
                </div>
            </div>

            <div class="table-responsive table-xfull"
                 v-bind:class="{'loading' : tblLoadingState==2 || loadingState==0}">
                <vuetable-my ref="vuetable"
                          api-url=""
                          :apiMode="false"
                          :fields="fields"
                          :dataManager="dataManager"
                          track-by="name"
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
                    <template slot="used" slot-scope="props">
                        <span class="label label-primary" v-if="props.rowData.used">Monitored</span>
                        <span class="label label-warning" v-else="">Not monitored</span>
                    </template>
                    <template slot="actions" slot-scope="props">
                        <button type="button" class="btn btn-subdom btn-success"
                                v-if="!props.rowData.used" v-on:click="addToMonitoring(props.rowData)"
                        >Add to monitoring</button>
                        <button type="button" class="btn btn-subdom disabled" disabled="disabled" v-else=""
                        >Monitoring</button>

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
                    <button type="button" class="btn btn-sm btn-success" :class="{'disabled': numSelected==0}"
                            :disabled="numSelected==0"
                            @click="addToMonitoringChecked()">
                        <i class="glyphicon glyphicon-plus" title="Add to monitoring"></i></button>
                </div>
                <span>Selected {{numSelected}} {{ pluralize('sub-domain', numSelected) }} </span>
                <button type="button" class="btn btn-sm pull-right btn-success" @click="downloadServerList" >
                    Download all sub-domains
                </button>
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
    import _ from 'lodash';
    import axios from 'axios';
    import Req from 'req';
    import Blob from 'w3c-blob';
    import FileSaver from 'file-saver';
    import toastr from 'toastr';

    import Vue from 'vue';
    import VueEvents from 'vue-events';

    import Vuetable from 'vuetable-2/src/components/Vuetable';
    import VuetablePagination from 'vuetable-2/src/components/VuetablePagination';
    import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo';
    import VuetablePaginationBootstrap from '../../components/partials/VuetablePaginationBootstrap';

    import CustomActions from '../partials/EditDeleteActions.vue';
    import FilterBar from '../partials/FilterBar';

    Vue.use(VueEvents);
    Vue.component('custom-actions', CustomActions);
    Vue.component('filter-bar', FilterBar);

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
                tblLoadingState: 0,

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
                        name: 'name',
                        sortField: 'name',
                        title: 'Domain name',
                    },
                    {
                        name: '__slot:actions',
                        title: 'Actions',
                        titleClass: 'text-center',
                        dataClass: 'text-center',
                        sortField: 'used',
                    }
                ],
                css: {
                    table: {
                        tableClass: 'table table-sub-list table-bordered table-striped table-hover',
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
                    {field: 'used', sortField: 'used', direction: 'asc'},
                    {field: 'scan_host', sortField: 'scan_host', direction: 'asc'}
                ],
                moreParams: {},

                results: null,
                dataProcessStart: null,
                processedData: null,
                numSelected: 0
            }
        },

        mounted() {
            this.$nextTick(function () {
                this.hookup();
            })
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
            pluralize,
            onPaginationData (paginationData) {
                this.$refs.pagination.setPaginationData(paginationData);
                this.$refs.paginationInfo.setPaginationData(paginationData);
            },
            onChangePage (page) {
                this.$refs.vuetable.changePage(page);
            },
            onCellClicked (data, field, event) {
                ;
            },
            onLoading(){
                if (this.tblLoadingState !== 0){
                    this.tblLoadingState = 2;
                }
            },
            onLoaded(){
                this.tblLoadingState = 1;
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

            renderPagination(h) {
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

            hookup(){
                setTimeout(this.loadData, 0);
            },

            loadData(){
                const onFail = (function(){
                    this.loadingState = -1;
                    toastr.error('Error while loading, please, try again later', 'Error');
                }).bind(this);

                const onSuccess = (function(data){
                    this.loadingState = 1;
                    this.results = data;
                    setTimeout(this.processData, 0);
                }).bind(this);

                this.loadingState = 0;
                axios.get('/home/subs/res')
                    .then(response => {
                        if (!response || !response.data) {
                            onFail();
                        } else if (response.data['status'] === 'success') {
                            onSuccess(response.data);
                        } else {
                            onFail();
                        }
                    })
                    .catch(e => {
                        console.log("Loading det sub failed: " + e);
                        onFail();
                    });
            },

            processData(){
                this.$nextTick(function () {
                    this.dataProcessStart = moment();
                    this.processResults();
                });
            },

            processResults() {
                const curTime = new Date().getTime() / 1000.0;
                this.processedData = _.castArray(this.results.subs);

                this.$forceUpdate();
                this.$emit('onProcessed');
                this.loadingState = 10;

                this.$nextTick(function () {
                    this.postLoad();
                    const processTime = moment().diff(this.dataProcessStart);
                    this.$refs.vuetable.refresh();
                    console.log('Subs det Processing finished in ' + processTime + ' ms');
                });
            },

            postLoad(){

            },

            cleanResults(){
                this.results = null;
                this.loadingState = 0;
                this.$emit('onReset');
            },

            //
            // Adding
            //

            addToMonitoring(rowData){
                ga('send', 'event', 'subdomains', 'add-monitor');
                this.addToMonitoringExt(rowData);
            },

            addToMonitoringChecked(rowData){
                ga('send', 'event', 'subdomains', 'add-monitor-selected');

                const selectedSubs = this.$refs.vuetable.getSelectedIds();
                this.addToMonitoringExt(selectedSubs, true);
            },

            addToMonitoringExt(rowData, isMore) {
                const newItem = isMore ? {'servers': rowData} : {'server': rowData.name};

                // TODO: refactor to use common code with AddServer.vue
                const onFail = (function(){
                    Req.bodyProgress(false);
                    toastr.error(isMore ?
                        'Error while adding the servers, please, try again later' :
                        'Error while adding the server, please, try again later', 'Error');
                }).bind(this);

                const onDuplicate = (function(){
                    Req.bodyProgress(false);
                    toastr.error('This host is already being monitored.', 'Already present');
                }).bind(this);

                const onTooMany = (function(data){
                    Req.bodyProgress(false);
                    toastr.error('We are sorry but you just reached maximum number of '
                        + data['max_limit'] + ' monitored servers.', 'Too many servers');
                }).bind(this);

                const onSuccess = (function(data){
                    rowData.used = true;
                    Req.bodyProgress(false);
                    if (isMore){
                        Vue.nextTick(() => {
                            this.$refs.vuetable.uncheckAll();
                        });
                    }

                    this.$emit('onServerAdded', data);
                    this.$events.fire('on-server-added', data);
                    toastr.success(isMore ?
                        'Server Added Successfully.' :
                        'Servers Added Successfully.', 'Success', {preventDuplicates: true});
                }).bind(this);

                Req.bodyProgress(true);
                axios.post('/home/servers/add' + (isMore ? 'More' : ''), newItem)
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
            },

            //
            // Data table filter & sorting
            //

            needRefresh(){
                setTimeout(this.loadData, 0);
            },

            dataManager(sort, pagination){
                let showData = this.processedData;

                // filtering
                if (this.moreParams && this.moreParams.filter){
                    showData = _.filter(showData, (item) => {
                        return item.name.search(this.moreParams.filter) >= 0;
                    });
                }

                // sorting - vuetable sort to orderBy
                showData = Req.vueOrderBy(showData, sort);

                // pagination
                [showData, pagination] = Req.vuePagination(showData, pagination);

                //noinspection UnnecessaryLocalVariableJS
                const ret = _.extend({data: showData}, pagination);
                return ret;
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
            downloadServerList() {
                const data = this.processedData;

                // Building the CSV from the Data two-dimensional array
                // Each column is separated by "," and new line "\n" for next row
                let acc = ['domain,added'];
                for (let cur of data) {
                    const dataString = _.join([cur.name, +cur.used]);
                    acc.push(dataString);
                }

                const csvContent = _.join(acc, '\n');
                const blob = new Blob([csvContent], {type: 'text/csv;charset=utf-8'});
                FileSaver.saveAs(blob, "sub-domains.csv");
            },
        },
        events: {
            'on-server-added' (data) {
                Vue.nextTick(() => this.needRefresh());
            },
            'on-server-updated'(data) {
                Vue.nextTick(() => this.needRefresh());
            },
            'on-server-deleted'(data) {
                Vue.nextTick(() => this.needRefresh());
            },
            'on-manual-refresh'(){
                Vue.nextTick(() => this.needRefresh());
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

    @media (max-width: 1023px) {
        .btn.btn-subdom {
            min-width: 140px;
        }
        .table.vuetable.table-sub-list .vuetable-th-slot-actions{
            width: 145px;
        }
    }

    @media (min-width: 1024px) {
        .btn.btn-subdom {
            min-width: 240px;
        }
        .table.vuetable.table-sub-list .vuetable-th-slot-actions{
            width: 245px;
        }
    }

</style>
