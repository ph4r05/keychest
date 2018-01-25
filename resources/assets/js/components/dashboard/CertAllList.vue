<template>
    <div class="row">
        <div class="xcol-md-12">
            <sbox cssBox="box-primary" :collapsed="true" :headerCollapse="true">
                <template slot="title">All certificates of your servers</template>

                <div class="form-group">
                    <p>The list shows all certificates in Certificate Transparency (CT) public logs ({{ len(certs) }}).</p>
                    <toggle-button v-model="includeExpired"
                                   id="chk-include-expired"
                                   color="#00a7d7"
                                   :labels="{checked: 'On', unchecked: 'Off'}"
                    />
                    <label for="chk-include-expired">Include expired CT certificates</label>
                </div>

                <all-certs-table :certs="certs"/>
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

    import DashboardAllCertsTable from './tables/CertsAllTable';

    Vue.use(VueEvents);
    Vue.use(ToggleButton);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    export default {
        name: 'all-certs',

        components: {
            'all-certs-table': DashboardAllCertsTable,
        },

        props: {
            /**
             * Input to display
             */
            certs: {
                type: Array,
                default() {
                    return []
                },
            },
        },

        data: function() {
            return {
                includeExpired: false,
            };
        },

        watch: {
            includeExpired(newVal, oldVal) {
                if (newVal !== oldVal) {
                    this.$emit('include-expired', newVal);
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
