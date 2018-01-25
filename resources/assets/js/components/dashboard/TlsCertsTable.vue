<template>
    <div class="table-responsive table-xfull">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th>Server names</th>
                <th>Issuer</th>
                <th colspan="2">Renew / plan renewal</th>
            </tr>
            </thead>

            <tbody>
            <tr v-for="cert in sortExpiry(tlsCerts)" v-if="cert.planCss">
                <td v-bind:class="cert.planCss.tbl">
                                        <span class="hidden">
                                            ID: {{ cert.id }}
                                            CNAME: {{ cert.cname }}
                                        </span>
                    <ul class="domain-list">
                        <li v-for="domain in cert.watch_hosts">
                            <template v-if="cert.cname === domain">{{ domain }} <small><em>(CN)</em></small></template>
                            <template v-else="">{{ domain }}</template>
                        </li>
                    </ul>
                </td>
                <td v-bind:class="cert.planCss.tbl">{{ cert.issuerOrgNorm }}</td>
                <td v-bind:class="cert.planCss.tbl">{{ cert.valid_to }}</td>
                <td v-bind:class="cert.planCss.tbl"
                    v-if="(momentu(cert.valid_to)<momentu())&&(len(cert.watch_hosts)<2)">
                    SERVER DOWN since {{ momentu(cert.valid_to).fromNow() }}</td>
                <td v-bind:class="cert.planCss.tbl"
                    v-else-if="(momentu(cert.valid_to)<momentu())&&(len(cert.watch_hosts)>1)">
                    SERVERS DOWN since {{ momentu(cert.valid_to).fromNow() }}</td>
                <td v-bind:class="cert.planCss.tbl"
                    v-else="">{{ momentu(cert.valid_to).fromNow() }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
    import _ from 'lodash';
    import moment from 'moment';
    import pluralize from 'pluralize';

    import Req from 'req';
    import util from './code/util';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VueScrollTo from 'vue-scrollto';
    import VeeValidate from 'vee-validate';
    import { mapFields } from 'vee-validate';

    Vue.use(VueEvents);
    Vue.use(VueScrollTo);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    export default {
        props: {
            /**
             * Input certs to display
             */
            tlsCerts: {
                type: Array,
                default() {
                    return []
                },
            },
        },

        methods: {
            take: util.take,
            len: util.len,
            utcTimeLocaleString: util.utcTimeLocaleString,
            utcTimeLocaleStringUs: Req.del(util.utcTimeLocaleStringUs, util),
            utcTimeLocaleDateString: util.utcTimeLocaleDateString,
            utcTimeLocaleDateStringUs: Req.del(util.utcTimeLocaleDateStringUs, util),
            curDateUsString: Req.del(util.curDateUsString, util),
            extendDateField: util.extendDateField,
            moment: util.moment,
            momentu: util.momentu,
            sortBy: util.sortBy,
            sortExpiry: util.sortExpiry,
            tblVal: util.tblVal,

            certIssuer: util.certIssuer,
        },
    }
</script>

<style scoped>

</style>
