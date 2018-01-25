<template>
    <div class="table-responsive table-xfull" style="margin-bottom: 10px">
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th>Provider</th>
                <th>Watched servers</th>
                <th>All issued certificates (CT)</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="curDat in certIssuerTableData">
                <td> {{ curDat[0] }} </td>
                <td> {{ curDat[1] }} </td>
                <td> {{ curDat[2] }} </td>
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
            certIssuerTableData: {
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
