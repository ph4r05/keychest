<template>
    <div class="row">
        <div class="xcol-md-12">
            <sbox cssBox="box-success" :headerCollapse="true">
                <template slot="title">Number of incidents per category</template>
                <p>The table shows a summary of the number of active incidents per category.
                    Further details are in the "Incidents" section of the dashboard.
                </p>
                <div class="table-responsive table-xfull">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                        <tr>
                            <!--<th>ID</th>-->
                            <th>Incident category</th>
                            <th>Number of active incidents</th>
                        </tr>
                        </thead>

                        <tbody>
                        <tr>
                            <td>DNS configuration issues</td>
                            <td>{{ lenDnsFailedLookups }}</td>
                        </tr>

                        <tr>
                            <td>Unreachable servers</td>
                            <td>{{ lenTlsErrors }}</td>
                        </tr>

                        <tr>
                            <td>Servers with configuration errors</td>
                            <td>{{ lenTlsInvalidTrust }}</td>
                        </tr>

                        <tr>
                            <td>Incorrect certificates</td>
                            <td>{{ lenTlsInvalidHostname }}</td>
                        </tr>

                        <tr>
                            <td>Expired certificates</td>
                            <td>{{ lenExpiredCertificates }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </sbox>
        </div>
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
        name: 'incident-summary',

        props: {
            /**
             * Input to display
             */
            lenDnsFailedLookups: {
                type: Number,
                default: 0,
            },

            lenTlsErrors: {
                type: Number,
                default: 0,
            },

            lenExpiredCertificates: {
                type: Number,
                default: 0,
            },

            lenTlsInvalidHostname: {
                type: Number,
                default: 0,
            },

            lenTlsInvalidTrust: {
                type: Number,
                default: 0,
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
        },
    }
</script>

<style scoped>

</style>
