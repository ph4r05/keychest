<template>
    <div>

        <div class="alert alert-info alert-waiting scan-alert" id="search-info"
             v-if="loadingState == 0">
            <span>Loading data, please wait...</span>
        </div>

        <div v-show="loadingState != 0">
            <div class="row">
                <div class="col-md-8">
                    <filter-bar
                            :globalEvt="false"
                            v-on:filter-set="onFilterSet"
                            v-on:filter-reset="onFilterReset"
                    ></filter-bar>
                </div>
            </div>

            <!--/home/subs/res-->
            <div class="table-responsive table-xfull" v-bind:class="{'loading' : loadingState==2}">
                <vuetable ref="vuetable"
                          api-url=""
                          :apiMode="false"
                          :fields="fields"
                          :dataManager="dataManager"
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
                ></vuetable>
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
    import _ from 'lodash';

    import Vue from 'vue';
    import VueEvents from 'vue-events';

    import Vuetable from 'vuetable-2/src/components/Vuetable';
    import VuetablePagination from 'vuetable-2/src/components/VuetablePagination';
    import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo';
    import VuetablePaginationBootstrap from '../../components/partials/VuetablePaginationBootstrap';

    import CustomActions from '../servers/CustomActions';
    import DetailRow from '../servers/DetailRow';
    import FilterBar from '../servers/FilterBar';

    Vue.use(VueEvents);
    Vue.component('custom-actions', CustomActions);
    Vue.component('my-detail-row', DetailRow);
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
                        name: '__sequence',
                        title: '#',
                        titleClass: 'text-right',
                        dataClass: 'text-right'
                    },
                    {
                        name: 'name',
                        sortField: 'name',
                        title: 'Host',
                    },
                    {
                        name: '__component:custom-actions',
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

                results: null,
                dataProcessStart: null,
                processedData: null,
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
                return (value === null) ? '' : moment(value, 'YYYY-MM-DD HH:mm').format(fmt);
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
                if (this.tblLoadingState !== 0){
                    this.tblLoadingState = 2;
                }
            },
            onLoaded(){
                this.tblLoadingState = 1;
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
            // Data table filter & sorting
            //

            dataManager(sort, pagination){
                let showData = this.processedData;

                // filtering
                if (this.moreParams && this.moreParams.filter){
                    showData = _.filter(showData, (item) => {
                        return item.name.search(this.moreParams.filter) >= 0;
                    });
                }

                // sorting - vuetable sort to orderBy
                const ordering = Req.vueSortToOrderBy(sort);
                showData = _.orderBy(showData, ordering[0], ordering[1]);

                // pagination
                pagination.total = _.size(showData);
                showData = _.chunk(showData, pagination.per_page)[pagination.current_page - 1];

                pagination.last_page = Math.ceil(pagination.total / pagination.per_page);
                pagination.to = _.min([pagination.from + pagination.per_page - 1, pagination.total]);

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
        },
        events: {
            'on-server-added' (data) {
                Vue.nextTick(() => this.$refs.vuetable.refresh());
            },
            'on-server-updated'(data) {
                Vue.nextTick(() => this.$refs.vuetable.refresh());
            },
            'on-delete-server'(data) {
                this.onDeleteServer(data);
            },
        }
    }
</script>
<style scoped>
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
