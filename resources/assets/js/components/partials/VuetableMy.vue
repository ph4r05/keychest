<script>
    import Vue from 'vue';
    import Vuetable from 'vuetable-2/src/components/Vuetable';
    import TableSelectMixin from './TableSelectMixin.vue';

    export default {
        extends: Vuetable,
        mixins:[
            TableSelectMixin
        ],
        components: {
            TableSelectMixin
        }
    }

</script>
