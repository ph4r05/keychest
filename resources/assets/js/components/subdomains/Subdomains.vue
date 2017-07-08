<template>
    <div class="subdomains-wrapper">
        <h3>Domains for automated server discovery</h3>
        <subdomain-watchers></subdomain-watchers>

        <h3>Discovered servers </h3>
        <subdomain-detected></subdomain-detected>
    </div>
</template>

<script>
    import axios from 'axios';
    import moment from 'moment';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import SubdomainWatchers from './SubdomainWatchers.vue';
    import SubdomainDetected from './DetectedSubdomains.vue';

    Vue.use(VueEvents);
    Vue.component('subdomain-watchers', SubdomainWatchers);
    Vue.component('subdomain-detected', SubdomainDetected);

    export default {
        data: function() {
            return {
                loadingState: 0,
                results: null,
                dataProcessStart: null,
            };
        },

        mounted() {
            this.$nextTick(function () {
                this.hookup();
            })
        },

        computed: {
            hasAccount(){
                return !this.Laravel.authGuest;
            },
        },

        watch: {

        },

        methods: {
            hookup(){
            },
        }
    }
</script>

<style>
    ul.domain-list {
        padding-left: 0;
    }

    ul.domain-list li {
        list-style-type: none;
    }

    .coma-list {
        display: inline;
        list-style: none;
        padding-left: 0;
    }

    .coma-list li {
        display: inline;
    }

    .coma-list li:after {
        content: ", ";
    }

    .coma-list li:last-child:after {
        content: "";
    }

    .fade-enter-active, .fade-leave-active {
        transition: opacity 1.0s
    }
    .fade-enter, .fade-leave-to /* .fade-leave-active in <2.1.8 */ {
        opacity: 0
    }

    .box-body > .table-xfull {
        margin-left: -10px;
        margin-right: -10px;
        margin-bottom: -10px;
        width: auto;
    }

    .box-body > .table-xfull > .table {
        margin-bottom: auto;
    }

    .box-body > .table-xfull > .table > thead > tr > th,
    .box-body > .table-xfull > .table > tbody > tr > td
    {
        padding-left: 12px;
    }

    .info-box-label {
        line-height: 80px;
        padding-left: 50px;
        font-size: 20px;
        font-weight: 400;
        color: #444;
    }

</style>

