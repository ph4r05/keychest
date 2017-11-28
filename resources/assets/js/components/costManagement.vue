<template>
    <div class="row">
        test
    </div>
</template>
<script>
    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VueRouter from 'vue-router';

    Vue.use(VueEvents);
    Vue.use(VueRouter);

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

