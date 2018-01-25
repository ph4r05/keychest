<template>
    <div class="table-responsive table-xfull">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th>Domain name</th>
                <th>You have to renew</th>
                <th>Expiration date</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="cur_whois in sortBy(whois, 'expires_at_utc')" v-if="cur_whois.expires_at_days <= 90">
                <td v-bind:class="cur_whois.planCss.tbl">
                    {{ cur_whois.domain }}
                </td>
                <td v-bind:class="cur_whois.planCss.tbl">
                    {{ momentu(cur_whois.expires_at_utc * 1000.0).fromNow() }}
                </td>
                <td v-bind:class="cur_whois.planCss.tbl">
                    {{ utcTimeLocaleDateString(cur_whois.expires_at_utc) }}
                </td>
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
             * Input to display
             */
            whois: {
                type: Object,
                default() {
                    return {}
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
