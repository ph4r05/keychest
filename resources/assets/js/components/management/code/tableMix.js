
import _ from 'lodash';

import Req from 'req';
import util from './util';

/**
 * Table mixing
 * Pagination rendering
 */
export default {

    methods: {

        /**
         * VueTable pagination renderer
         * @param h
         * @returns {*}
         */
        renderPagination(h) {
            console.log('pagpag');
            return h(
                'div',
                { class: {'vuetable-pagination': true} },
                [
                    h('vuetable-pagination-info', { ref: 'paginationInfo', props: { css: this.css.paginationInfo } }),
                    h('vuetable-pagination-bootstrap', {
                        ref: 'pagination',
                        class: { 'pull-right': true },
                        props: {
                        },
                        on: {
                            'vuetable-pagination:change-page': this.onChangePage
                        }
                    })
                ]
            )
        },

    }

};
