<template>
    <div class="table-responsive table-xfull" style="margin-bottom: 10px">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th rowspan="2">No of server names<br/> in certificates</th>
                <th colspan="3">Certificates on watched servers</th>
                <th colspan="3">All issued certificates (CT)</th>
                <!--<th colspan="3">Watched SLDs</th>-->
                <!--<th colspan="3">SLDs in global logs</th> -->
            </tr>
            <tr>
                <th>Let&#39;s Encrypt</th>
                <th>Other certificates</th>
                <th><i>Number of all issuers</i></th>

                <th>Let&#39;s Encrypt</th>
                <th>Other certificates</th>
                <th><i>Number of all issuers</i></th>

                <!--<th>Certs</th>-->
                <!--<th>Issuers</th>-->
                <!--<th>LE</th>-->

                <!--<th>Certs</th>-->
                <!--<th>Issuers</th>-->
                <!--<th>LE</th>-->
            </tr>
            </thead>

            <tbody>
            <tr v-for="group in certDomainsTableData">
                <td>{{ getCountCategoryLabelTbl(group[0]) }}</td>

                <td>{{ tblVal(group[1][0].leCnt) }}</td>
                <td v-if="isNaN(tblVal(group[1][0].leCnt))">{{ tblVal(group[1][0].size) }}</td>
                <td v-else="">{{ tblVal(group[1][0].size) - tblVal(group[1][0].leCnt) }}</td>
                <td><i>{{ tblVal(group[1][0].distIssuers) }}</i></td>

                <td>{{ tblVal(group[1][1].leCnt) }}</td>
                <td v-if="isNaN(tblVal(group[1][1].leCnt))">{{ tblVal(group[1][1].size) }}</td>
                <td v-else="">{{ tblVal(group[1][1].size) - tblVal(group[1][1].leCnt) }}</td>
                <td><i>{{ tblVal(group[1][1].distIssuers) }}</i></td>

                <!--<td>{{ tblVal(group[1][2].size) }}</td>-->
                <!--<td>{{ tblVal(group[1][2].distIssuers) }}</td>-->
                <!--<td>{{ tblVal(group[1][2].leCnt) }}</td>-->

                <!--<td>{{ tblVal(group[1][3].size) }}</td>-->
                <!--<td>{{ tblVal(group[1][3].distIssuers) }}</td>-->
                <!--<td>{{ tblVal(group[1][3].leCnt) }}</td>-->
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    import _ from 'lodash';
    import pluralize from 'pluralize';

    import Req from 'req';
    import util from './code/util';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VeeValidate from 'vee-validate';
    import { mapFields } from 'vee-validate';

    Vue.use(VueEvents);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    export default {
        name: "cert-domains-table",

        props: {
            /**
             * Input certs to display
             */
            certDomainsTableData: {
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