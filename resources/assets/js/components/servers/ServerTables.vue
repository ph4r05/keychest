<template>
  <div>
    <filter-bar></filter-bar>
    <add-server></add-server>
    <edit-server></edit-server>

    <div class="table-responsive">
    <vuetable ref="vuetable"
      api-url="/home/servers/get"
      :fields="fields"
      pagination-path=""
      :css="css.table"
      :sort-order="sortOrder"
      :multi-sort="true"
      :per-page="100"
      :append-params="moreParams"
      @vuetable:cell-clicked="onCellClicked"
      @vuetable:pagination-data="onPaginationData"
    ></vuetable>
    </div>

    <div class="vuetable-pagination">
      <vuetable-pagination-info ref="paginationInfo"
        info-class="pagination-info"
      ></vuetable-pagination-info>
      <vuetable-pagination ref="pagination"
        :css="css.pagination"
        :icons="css.icons"
        @vuetable-pagination:change-page="onChangePage"
      ></vuetable-pagination>
    </div>
  </div>
</template>
<script>
import accounting from 'accounting';
import moment from 'moment';
import Vuetable from 'vuetable-2/src/components/Vuetable';
import VuetablePagination from 'vuetable-2/src/components/VuetablePagination';
import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo';
import Vue from 'vue';
import VueEvents from 'vue-events';

import CustomActions from './CustomActions';
import DetailRow from './DetailRow';
import FilterBar from './FilterBar';
import AddServer from './AddServer.vue';
import EditServer from './EditServer.vue';

Vue.use(VueEvents);
Vue.component('custom-actions', CustomActions);
Vue.component('my-detail-row', DetailRow);
Vue.component('filter-bar', FilterBar);
Vue.component('add-server', AddServer);
Vue.component('edit-server', EditServer);

export default {
    components: {
        Vuetable,
        VuetablePagination,
        VuetablePaginationInfo,
    },
    data () {
        return {
            fields: [
                {
                    name: '__sequence',
                    title: '#',
                    titleClass: 'text-right',
                    dataClass: 'text-right'
                },
                {
                    name: 'scan_host',
                    sortField: 'scan_host',
                    title: 'Host',
                },
                {
                    name: 'scan_port',
                    sortField: 'scan_port',
                    title: 'Port',
                },
                {
                    name: 'scan_scheme',
                    sortField: 'scan_scheme',
                    title: 'Scheme',
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
                    name: 'updated_at',
                    title: 'Update',
                    sortField: 'updated_at',
                    titleClass: 'text-center',
                    dataClass: 'text-center',
                    callback: 'formatDate|DD-MM-YYYY'
                },
                {
                    name: 'last_scan_at',
                    title: 'Last scan',
                    sortField: 'last_scan_at',
                    titleClass: 'text-center',
                    dataClass: 'text-center',
                    callback: 'formatDate|DD-MM-YYYY'
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
                    wrapperClass: 'pagination',
                    activeClass: 'active',
                    disabledClass: 'disabled',
                    pageClass: 'page',
                    linkClass: 'link',
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
            moreParams: {}
        }
    },

    methods: {
        allcap (value) {
            return value.toUpperCase()
        },
        formatNumber (value) {
            return accounting.formatNumber(value, 2)
        },
        formatDate (value, fmt = 'DD-MM-YYYY') {
            return (value === null) ? '' : moment(value, 'YYYY-MM-DD').format(fmt);
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
        onDeleteServer(data){
            swal({
                title: 'Are you sure?',
                text: "Server will be permanently removed",
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes'
            }).then((function () {
                this.onDeleteServerConfirmed(data);
            }).bind(this)).catch(() => {});
        },
        onDeleteServerConfirmed(data){
            const onFail = (function(){
                this.moreParams.deleteState = -1;
                swal('Delete error', 'Server delete failed :(', 'error');
            }).bind(this);

            const onSuccess = (function(data){
                this.moreParams.deleteState = 1;
                Vue.nextTick(() => this.$refs.vuetable.refresh());
                this.$emit('onServerDeleted', data);
                this.$events.fire('on-server-deleted', data);
                toastr.success('Server deleted successfully.', 'Success');
            }).bind(this);

            this.moreParams.deleteState = 2;
            axios.post('/home/servers/del', data)
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
    },
    events: {
        'filter-set' (filterText) {
            this.moreParams = {
                filter: filterText
            };
            Vue.nextTick(() => this.$refs.vuetable.refresh())
        },
        'filter-reset' () {
            this.moreParams = {};
            Vue.nextTick(() => this.$refs.vuetable.refresh());
        },
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
</style>
