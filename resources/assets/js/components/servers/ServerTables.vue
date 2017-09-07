<template>
  <div>

    <div class="alert alert-info alert-waiting scan-alert" id="search-info"
         v-if="loadingState == 0">
      <span>Loading data, please wait...</span>
    </div>

    <div v-show="loadingState != 0">
      <h3>Monitored Servers <help-trigger id="serversInfoModal"></help-trigger></h3>
      <server-info></server-info>

      <div class="row">
        <div class="col-md-7">
          <add-server></add-server>
        </div>
        <div class="col-md-5">
          <filter-bar
                  :globalEvt="false"
                  v-on:filter-set="onFilterSet"
                  v-on:filter-reset="onFilterReset"
          ></filter-bar>
        </div>
      </div>

      <edit-server ref="editServer"></edit-server>

      <div class="table-responsive table-xfull" v-bind:class="{'loading' : loadingState==2}">
      <vuetable-my ref="vuetable"
        api-url="/home/servers/get"
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
        <template slot="errors" scope="props">
          <span class="label label-danger" v-if="props.rowData.dns_error">DNS</span>
          <span class="label" v-bind:class="{
              'label-danger': props.rowData.tls_errors == props.rowData.tls_all,
              'label-warning': props.rowData.tls_errors < props.rowData.tls_all
              }" v-if="props.rowData.tls_errors > 0"><abbr v-bind:title="tlsTitle(props.rowData)" class="initialism">TLS</abbr>
          </span>
          <span class="label label-success" v-if="!props.rowData.dns_error && props.rowData.tls_errors == 0">ON</span>
        </template>
        <template slot="dns" scope="props">
          <div v-if="!props.rowData.dns_error">
            <span class="label label-primary" title="IPv4">{{props.rowData.dns_num_ipv4}}</span>
            <span class="label label-success" title="IPv6">{{props.rowData.dns_num_ipv6}}</span>
          </div>
          <span v-else="">-</span>
        </template>
        <template slot="actions" scope="props">
          <div class="custom-actions">
            <button class="btn btn-sm btn-primary" @click="onEditServer(props.rowData)"><i class="glyphicon glyphicon-pencil"></i></button>
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
                  @click="onDeleteServers()">
            <i class="glyphicon glyphicon-trash" title="Delete"></i></button>
        </div>
        <span>Selected {{numSelected}} {{ numSelected | pluralize('server') }} </span>
      </div>

      <div class="vuetable-pagination form-group">
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
import Vue2Filters from 'vue2-filters';

import Vuetable from 'vuetable-2/src/components/Vuetable';
import VuetablePagination from 'vuetable-2/src/components/VuetablePagination';
import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo';
import VuetablePaginationBootstrap from '../../components/partials/VuetablePaginationBootstrap';

import FilterBar from '../partials/FilterBar.vue';
import AddServer from './AddServer.vue';
import EditServer from './EditServer.vue';
import ServerInfo from './ServerInfo.vue';

Vue.use(VueEvents);
Vue.use(Vue2Filters);
Vue.component('filter-bar', FilterBar);
Vue.component('add-server', AddServer);
Vue.component('edit-server', EditServer);
Vue.component('server-info', ServerInfo);

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
                    title: 'Domain name',
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
                    name: '__slot:dns',
                    title: 'IP v4/v6',
                    sortField: 'dns_num_res',
                    titleClass: 'text-center',
                    dataClass: 'text-center',
                },
                {
                    name: '__slot:errors',
                    title: 'Errors',
                    sortField: 'dns_error',
                    titleClass: 'text-center',
                    dataClass: 'text-center',
                },
                {
                    name: 'last_scan_at',
                    title: 'Last scan',
                    sortField: 'last_scan_at',
                    titleClass: 'text-center',
                    dataClass: 'text-center',
                    callback: 'formatDate|DD-MM-YYYY HH:mm'
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
            numSelected: 0
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
        tlsTitle(rowData){
            return `TLS checks failed for ${rowData.tls_errors} out of ${rowData.tls_all} IP addresses`;
        },
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
            if (this.loadingState != 0){
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
        invertCheckBoxes(){
            this.$refs.vuetable.invertCheckBoxes();
        },
        uncheckAll(){
            this.$refs.vuetable.uncheckAllPages();
        },
        onEditServer(data){
            this.$refs.editServer.onEditServer(data);
        },
        onDeleteServer(data){
            swal({
                title: 'Please confirm removal',
                text: "The selected server will be removed. If it is from an Active Domain, you will still "+
                      "see it in the 'Active Domain tab'.",
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Remove'
            }).then((function () {
                this.onDeleteServerConfirmed(data);
            }).bind(this)).catch(() => {});
        },
        onDeleteServers(){
            swal({
                title: 'Please confirm removal',
                text: pluralize('server', this.numSelected, true) + " will be removed. You will still see servers "+
                      "from Active Domains in the 'Active Domain tab'.",
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Remove'
            }).then((function () {
                this.onDeleteServerConfirmed({'ids': this.$refs.vuetable.selectedTo}, true);
            }).bind(this)).catch(() => {});
        },
        onDeleteServerConfirmed(data, isMore){
            const onFail = (function(){
                this.moreParams.deleteState = -1;
                swal('Delete error', 'Server delete failed :(', 'error');
            }).bind(this);

            const onSuccess = (function(data){
                this.moreParams.deleteState = 1;
                Vue.nextTick(() => {
                    this.$refs.vuetable.refresh();
                    if (isMore){
                        this.$refs.vuetable.uncheckAll();
                    }
                });

                this.$emit('onServerDeleted', data);
                this.$events.fire('on-server-deleted', data);
                toastr.success(isMore ?
                    'Servers deleted successfully.':
                    'Server deleted successfully.', 'Success');
            }).bind(this);

            this.moreParams.deleteState = 2;
            axios.post('/home/servers/del' + (isMore ? 'More' : ''), data)
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
