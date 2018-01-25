<template>
    <div class="table-responsive table-xfull">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th>Server name</th>
                <th>Certificate issuers</th>
                <th>Expiration date</th>
                <th>Last failure</th>
                <!--<th>ID</th> -->
            </tr>
            </thead>
            <tbody>
            <tr v-for="cert in sortBy(expiredCertificates, 'expires_at_utc')" class="danger">
                <td>
                    <span class="hidden">
                        ID: {{ cert.id }}
                    </span>
                    <ul class="domain-list">
                        <li v-for="domain in cert.watch_hosts">
                            <template v-if="cert.cname === domain">{{ domain }}
                                <small><em>(CN)</em></small>
                            </template>
                            <template v-else="">{{ domain }}</template>
                        </li>
                    </ul>
                </td>
                <td>{{ cert.issuerOrgNorm }}</td>
                <td>{{ utcTimeLocaleString(cert.valid_to_utc) }}
                    ({{ momentu(cert.valid_to_utc * 1000.0).fromNow() }})
                </td>
                <td>{{ utcTimeLocaleString(cert.last_scan_at_utc) }}</td>
                <!--<td>{{ cert.id }}</td>-->
            </tr>
            </tbody>
        </table>
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
        name: "cert-expired-table",

        props: {
            /**
             * Input certs to display
             */
            expiredCertificates: {
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
            getCertHostPorts: util.getCertHostPorts,
            getCountCategoryLabelTbl: Req.del(util.getCountCategoryLabelTbl, util),
        },
    }
</script>

<style scoped>

</style>