<template>
    <div class="management-root">
        <router-view></router-view>
    </div>
</template>
<script>
    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VueRouter from 'vue-router';

    import Management from './management.vue';
    import AddHost from './management/AddHost.vue';

    Vue.use(VueEvents);
    Vue.use(VueRouter);

    const routes = [
        {
            path: '/',
            component: Management,
            children: [

            ]
        },
        {
            path: '/addHost',
            name: 'addHost',
            component: AddHost,
        }
    ];

    const router = window.VueRouter; // type: VueRouter
    router.addRoutes(routes);

    export default {
        data () {
            return {

            }
        },

        methods: {
            refresh(){

            }
        },
    }
</script>
<style>
</style>
