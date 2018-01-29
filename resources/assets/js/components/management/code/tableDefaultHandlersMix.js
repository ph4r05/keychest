
import _ from 'lodash';

import Req from 'req';
import util from './util';

/**
 * Table mixing
 * Default handlers
 */
export default {

    methods: {

        onPaginationData (paginationData) {
            this.$refs.pagination.setPaginationData(paginationData);
            this.$refs.paginationInfo.setPaginationData(paginationData);
        },

        onChangePage (page) {
            this.$refs.vuetable.changePage(page);
        },

        onDetailToggle (data) {
            this.$refs.vuetable.toggleDetailRow(data.rowData.id);
        },

        invertCheckBoxes(){
            this.$refs.vuetable.invertCheckBoxes();
        },

        uncheckAll(){
            this.$refs.vuetable.uncheckAllPages();
        },

    }

};
