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

    Vue.use(VueEvents);
    Vue.use(VueRouter);

    const routes = [
        {
            path: '/',
            name: 'management',
            component: Management,
            children: [

            ],
        },
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
