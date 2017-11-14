<template>
    <div class="row">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">Managed hosts</a></li>
                <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">Groups</a></li>
                <li class=""><a href="#tab_3" data-toggle="tab" aria-expanded="false">Services</a></li>
                <li class="pull-right"><a href="#" class="text-muted" title="Refresh" v-on:click="refresh"><i class="fa fa-refresh"></i></a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">
                    <mgmt-hosts></mgmt-hosts>
                </div>
                <!-- /.tab-pane -->
                <div class="tab-pane" id="tab_2">

                </div>
                <!-- /.tab-pane -->
                <div class="tab-pane" id="tab_3">

                </div>
                <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
        </div>
    </div>
</template>
<script>
    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VueRouter from 'vue-router';

    import ManagementHosts from './management/Hosts.vue';

    Vue.use(VueEvents);
    Vue.use(VueRouter);

    Vue.component('mgmt-hosts', ManagementHosts);

    const router = window.VueRouter; // type: VueRouter
    export default {
        data () {
            return {
                loadingState: 0,
            }
        },

        methods: {
            refresh(){
                this.$events.fire('on-manual-refresh');
            }
        },
    }
</script>
<style>
</style>
