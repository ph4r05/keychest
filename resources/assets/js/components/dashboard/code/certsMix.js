
import _ from 'lodash';

import Req from 'req';
import util from './util';

/**
 * Dashboard mixin for computed properties.
 * Processes this.results with loaded dashboard data form the server,
 * builds various views for further rendering.
 *
 * @uses this.results
 * @uses this.includeExpired
 * @type {{computed: {tlsCertsIdsMap(): *, cdnCerts(): *, tlsCerts(): *, allCerts(): *, certs(): *, whois(): *, dns(): *, tls(): *}}}
 */
export default {

    computed: {
        tlsCertsIdsMap() {
            if (!this.results || !this.results.watch_to_tls_certs) {
                return {};
            }

            return Req.listToSet(_.uniq(_.flattenDeep(_.values(this.results.watch_to_tls_certs))));
        },

        cdnCerts() {
            if (!this.results || !this.results.tls || !this.results.certificates) {
                return {};
            }

            return util.cdnCerts(this.results.tls, this.results.certificates);
        },

        tlsCerts() {
            return _.map(_.keys(this.tlsCertsIdsMap), x => {
                return this.results.certificates[x];
            });
        },

        allCerts() {
            if (!this.results || !this.results.certificates) {
                return {};
            }

            return this.results.certificates;
        },

        certs() {
            if (!this.results || !this.results.certificates) {
                return {};
            }

            return _.filter(this.results.certificates, x => {
                return this.includeExpired || (x.id in this.tlsCertsIdsMap) || (x.valid_to_days >= -28);
            });
        },

        whois() {
            if (this.results && this.results.whois) {
                return this.results.whois;
            }
            return {};
        },

        dns() {
            if (this.results && this.results.dns) {
                return this.results.dns;
            }
            return {};
        },

        tls() {
            if (this.results && this.results.tls) {
                return this.results.tls;
            }
            return {};
        },
    },

};
