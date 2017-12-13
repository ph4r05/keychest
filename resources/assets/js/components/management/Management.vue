<template>
    <div class="row">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">Managed hosts</a></li>
                <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">Groups</a></li>
                <li class=""><a href="#tab_3" data-toggle="tab" aria-expanded="false">Services</a></li>
                <li class=""><a href="#tab_4" data-toggle="tab" aria-expanded="false">Solutions</a></li>
                <li class="pull-right"><a href="#" class="text-muted" title="Refresh" v-on:click="refresh"><i class="fa fa-refresh"></i></a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">
                    <mgmt-hosts></mgmt-hosts>
                </div>
                <!-- /.tab-pane -->
                <div class="tab-pane" id="tab_2">
                    <h3>Host group list</h3>
                </div>
                <!-- /.tab-pane -->
                <div class="tab-pane" id="tab_3">
                    <mgmt-services></mgmt-services>
                </div>
                <!-- /.tab-pane -->
                <div class="tab-pane" id="tab_4">
                    <h3>Solutions / endpoints</h3>
                </div>
                <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
        </div>
    </div>
</template>
<script>
    import _ from 'lodash';
    import Req from 'req';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VueRouter from 'vue-router';

    import ManagementHosts from './Hosts.vue';
    import ManagementServices from './Services.vue';

    import ChangeHost from './ChangeHost.vue';
    import ChangeService from './ChangeService.vue';

    Vue.use(VueEvents);
    Vue.use(VueRouter);

    Vue.component('mgmt-hosts', ManagementHosts);
    Vue.component('mgmt-services', ManagementServices);

    const router = window.VueRouter; // type: VueRouter
    const routes = [
        {
            path: '/addHost',
            name: 'addHost',
            component: ChangeHost,
            meta: {
                editMode: false,
                tabCode: 'mgmt',
                tab:  1,
                parent: {name: 'management'},
            },
        },

        {
            path: '/editHost/:id',
            name: 'editHost',
            component: ChangeHost,
            props: true,
            meta: {
                editMode: true,
                tabCode: 'mgmt',
                tab:  1,
                parent: {name: 'management'},
            },
        },

        {
            path: '/addService',
            name: 'addService',
            component: ChangeService,
            meta: {
                editMode: false,
                tabCode: 'mgmt',
                tab:  3,
                parent: {name: 'management'}
            },
        },

        {
            path: '/editService/:id',
            name: 'editService',
            component: ChangeService,
            props: true,
            meta: {
                editMode: true,
                tabCode: 'mgmt',
                tab:  3,
                parent: {name: 'management'}
            },
        },
    ];
    router.addRoutes(routes);

    // BUG: this closure slows down the navigation a bit from some reason
    router.afterEach((to, fromr) => {
        if (fromr){
            fromr.meta.predecessor = null;
            to.meta.predecessor = fromr.meta;
        }
        console.log('mgmt-aftereach-done');
    });

    export default {
        data () {
            return {
                loadingState: 0,
            }
        },

        mounted() {
            this.$nextTick(() => {
                this.hookup();
            });
        },

        methods: {
            refresh(){
                this.$events.fire('on-manual-refresh');
            },

            hookup(){
                // If the previous link defines the tab which should be displayed, switch the tab.
                const pred = _.get(this.$route, 'meta.predecessor');
                if (pred && pred.tabCode && pred.tabCode === 'mgmt' && pred.tab){
                    Req.switchTab('tab_' + pred.tab);
                }
            },
        },
    }
</script>
<style>
</style>
