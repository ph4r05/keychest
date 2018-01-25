<template>
    <div class="table-responsive table-xfull">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th>Server name</th>
                <th>Address</th>
                <th>Cause</th>
                <th>Time of detection</th>
                <th>Last failure</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="tls in sortBy(tlsInvalidTrust, 'created_at_utc')" class="danger">
                <td>
                    <span class="hidden">
                        ID: {{ tls.id }}
                    </span>
                    {{ tls.url_short }}
                </td>
                <td>{{ tls.ip_scanned }}</td>
                <td>
                    <ul class="domain-list">
                        <li v-if="tls.host_cert && tls.host_cert.is_self_signed">Self-signed certificate</li>
                        <li v-if="tls.host_cert && tls.host_cert.is_ca">CA certificate</li>
                        <li v-if="tls.host_cert && len(tls.certs_ids) > 1">Validation failed</li>
                        <li v-else-if="len(tls.certs_ids) === 1">Incomplete trust chain</li>
                        <li v-else-if="len(tls.certs_ids) === 0">No certificate</li>
                        <li v-else-if="tls.host_cert">Untrusted certificate</li>
                        <li v-else="">No host certificate</li>
                    </ul>
                </td>
                <td>{{ utcTimeLocaleString(tls.created_at_utc) }}
                    ({{ momentu(tls.created_at_utc * 1000.0).fromNow() }})</td>
                <td>{{ utcTimeLocaleString(tls.last_scan_at_utc) }}</td>
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
    import util from '../code/util';

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
            tlsInvalidTrust: {
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
