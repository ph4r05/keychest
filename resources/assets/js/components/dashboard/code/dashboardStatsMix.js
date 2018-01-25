
import _ from 'lodash';

import Req from 'req';
import util from './util';

/**
 * Dashboard mixin for computed properties.
 * Generates dashboard misc stats for widgets.
 *
 * @uses this.certs
 * @uses this.tlsCerts
 * @uses this.results
 */
export default {
    computed: {
        numHiddenCerts(){
            return Number(_.size(this.certs) - _.size(this.tlsCerts));
        },

        numExpiresSoon(){
            return Number(_.sumBy(this.tlsCerts, cur => {
                return (cur.valid_to_days <= 28 && cur.valid_to_days >= -28);
            }));
        },

        numExpiresNow(){
            return Number(_.sumBy(this.tlsCerts, cur => {
                return (cur.valid_to_days <= 8 && cur.valid_to_days >= -28);
            }));
        },

        numWatches(){
            return this.results ? _.size(this.results.watches) : 0;
        },
    },

};
