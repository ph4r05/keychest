<template>
    <div class="row">
        <div class="xcol-md-12">
            <sbox cssBox="box-primary" :collapsed="true" :headerCollapse="true">
                <template slot="title">Certificates under your management</template>
                <div class="form-group">
                    <p>This is a list of all certificates that you control and are responsible for renewals.
                        You can choose to see only certificates correctly installed on your server,
                        or all certificates issued to your servers.</p>
                    <toggle-button v-model="includeNotVerified"
                                   id="chk-include-notverified"
                                   color="#00a7d7"
                                   disabled="disabled"
                                   :labels="{checked: 'On', unchecked: 'Off'}"
                    />
                    <label for="chk-include-notverified">Include certificates not verified from your servers</label>
                </div>

                <tls-certs-table :tlsCerts="tlsCerts"/>
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
    import ToggleButton from 'vue-js-toggle-button';
    import VeeValidate from 'vee-validate';
    import { mapFields } from 'vee-validate';

    import DashboardTlsCertsTable from './tables/CertsTlsTable';

    Vue.use(VueEvents);
    Vue.use(ToggleButton);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    export default {
        name: 'tls-certs',

        components: {
            'tls-certs-table': DashboardTlsCertsTable,
        },

        props: {
            /**
             * Input to display
             */
            tlsCerts: {
                type: Array,
                default() {
                    return []
                },
            },
        },

        data: function() {
            return {
                includeNotVerified: false,
            };
        },

        watch: {
            includeNotVerified(newVal, oldVal) {
                if (newVal !== oldVal) {
                    this.$emit('include-not-verified', newVal);
                }
            }
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
