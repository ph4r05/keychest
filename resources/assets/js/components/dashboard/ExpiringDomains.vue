<template>
    <div>
        <!-- Expiring domains -->
        <div v-if="displayExpiringDomains && showExpiringDomains" class="row">
            <div class="xcol-md-12">
                <sbox cssBox="box-success" :headerCollapse="true">
                    <template slot="title">Domain name expiration dates</template>
                    <p>The following domain names' registration expires within 90 days.</p>

                    <expiring-domains-table :whois="whois"/>
                </sbox>
            </div>
        </div>

        <!-- Domains without expiration date detected - important, not to mislead it is fine -->
        <div v-if="displayUnknownExpiration && showDomainsWithUnknownExpiration" class="row">
            <div class="xcol-md-12">
                <sbox cssBox="box-warning" :headerCollapse="true">
                    <template slot="title">Domains with unknown expiration</template>
                    <p>We were unable to detect expiration domain date for the following domains:</p>

                    <unknown-expiration-domains-table :whois="whois"/>
                </sbox>
            </div>
        </div>
    </div>
</template>

<script>
    import _ from 'lodash';

    import Req from 'req';
    import util from './code/util';

    import Vue from 'vue';
    import VueEvents from 'vue-events';
    import VeeValidate from 'vee-validate';
    import { mapFields } from 'vee-validate';

    import DashboardExpiringDomainsTable from './tables/ExpiringDomainsTable';
    import DashboardUnknownExpirationDomainsTable from './tables/UnknownExpirationDomainsTable';

    Vue.use(VueEvents);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    export default {
        name: 'expiring-domains',

        components: {
            'expiring-domains-table': DashboardExpiringDomainsTable,
            'unknown-expiration-domains-table': DashboardUnknownExpirationDomainsTable,
        },

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

            displayExpiringDomains: {
                type: Boolean,
                default: true,
            },

            displayUnknownExpiration: {
                type: Boolean,
                default: true,
            },
        },

        computed: {
            showExpiringDomains(){
                return _.reduce(this.whois, (acc, cur) => {
                    return acc + (cur.expires_at_days <= 90);
                }, 0) > 0;
            },

            showDomainsWithUnknownExpiration(){
                return _.reduce(this.whois, (acc, cur) => {
                    return acc + (!cur.expires_at_days);
                }, 0) > 0;
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
