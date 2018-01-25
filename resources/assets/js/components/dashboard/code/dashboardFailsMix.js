
import _ from 'lodash';

import Req from 'req';
import util from './util';

/**
 * Dashboard mixin for computed properties.
 * Generates dashboard failed stats / datasets (e.g., dns failed lookups)
 *
 * @uses this.dns
 * @uses this.tls
 * @uses this.tlsCerts
 */
export default {
    computed: {
        dnsFailedLookups(){
            const r = _.filter(this.dns, x => {
                return x && x.status !== 1;
            });
            return _.sortBy(r, [x => { return x.domain; }]);
        },

        tlsErrors(){
            return util.tlsErrors(this.tls);
        },

        expiredCertificates(){
            return _.filter(this.tlsCerts, x => {
                return x.is_expired;
            });
        },

        tlsInvalidTrust(){
            return _.filter(this.tls, x => {
                return x && x.status === 1 && !x.valid_path;
            });
        },

        tlsInvalidHostname(){
            return _.filter(this.tls, x => {
                return x && x.status === 1 && x.valid_path && !x.valid_hostname;
            });
        },
    },

};
