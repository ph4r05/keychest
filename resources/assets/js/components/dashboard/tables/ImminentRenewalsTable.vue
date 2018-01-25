<template>
    <div>
        <div class="table-responsive table-xfull">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                <tr>
                    <th colspan="2">Renew before</th>
                    <th>Server names</th>
                    <th>Last update</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="grp in imminentRenewalCerts">
                    <td colspan="2" v-bind:class="grp[0].planCss.tbl" v-if="momentu(grp[0].valid_to_utc * 1000.0)<momentu()">
                        SERVER DOWN since {{ momentu(grp[0].valid_to_utc * 1000.0).fromNow() }} </td>
                    <td colspan="2" v-bind:class="grp[0].planCss.tbl" v-else="">
                        {{ utcTimeLocaleDateString(grp[0].valid_to_utc) }}
                        ({{ momentu(grp[0].valid_to_utc * 1000.0).fromNow() }}) </td>
                    <td v-bind:class="grp[0].planCss.tbl">
                        <ul class="coma-list" v-if="len(getCertHostPorts(grp)) > 0">
                            <li v-for="domain in getCertHostPorts(grp)">{{ domain }}</li>
                        </ul>
                        <span v-else="">No domains found</span>
                    </td>
                    <td v-bind:class="grp[0].planCss.tbl">{{ utcTimeLocaleString(grp[0].last_scan_at_utc) }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    import _ from 'lodash';
    import pluralize from 'pluralize';

    import Req from 'req';
    import util from '../code/util';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VeeValidate from 'vee-validate';
    import { mapFields } from 'vee-validate';

    Vue.use(VueEvents);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    export default {
        name: "imminent-renewals-table",

        props: {
            /**
             * Input certs to display
             */
            imminentRenewalCerts: {
                type: Array,
                default() {
                    return []
                },
            },
        },

        methods: {
            hookup(){

            },

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
            getCertHostPorts: util.getCertHostPorts,
            getCountCategoryLabelTbl: Req.del(util.getCountCategoryLabelTbl, util),
        },
    }
</script>

<style scoped>

</style>