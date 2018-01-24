import _ from 'lodash';
import moment from 'moment';
import Req from 'req';

export default {

    chartColors: [
        '#00c0ef',
        '#f39c12',
        '#00a65a',
        '#f56954',
        '#3c8dbc',
        '#d2d6de',
        '#ff6384',
        '#d81b60',
        '#ffcd56',
        '#4bc0c0',
        '#36a2eb',
        '#9966ff',
        '#001F3F',
        '#605ca8',
        '#ffde56',
        '#c43833',
    ],

    countCategories: [1, 2, 5, 10, 25, 50, 100, 250, 500, 1000],

    //
    // Data utils
    //

    take(x, len){
        return _.take(x, len);
    },

    len(x) {
        if (x){
            return _.size(x);
        }
        return 0;
    },

    moment(x){
        return moment(x);
    },

    momentu(x){
        return moment.utc(x);
    },

    extendDateField(obj, key) {
        if (_.isEmpty(obj[key]) || _.isUndefined(obj[key])){
            obj[key+'_utc'] = undefined;
            obj[key+'_days'] = undefined;
            return;
        }

        const utc = moment.utc(obj[key]).unix();
        obj[key+'_utc'] = utc;
        obj[key+'_days'] = Math.round(10 * (utc - moment().utc().unix()) / 3600.0 / 24.0) / 10;
    },

    sortBy(x, fld){
        return _.sortBy(x, [ (o) => { return o[fld]; } ] );
    },

    sortExpiry(x){
        return _.sortBy(x, [ (o) => { return o.valid_to_utc; } ] );
    },

    tblVal(x){
        return x ? x : '-';
    },

    //
    // Certs
    //

    /**
     * Returns numerical index [0,4] depending on a week the certificate is valid.
     * @param x
     * @returns {number}
     */
    week4grouper(x){
        if (x.valid_to_days <= 0 && x.valid_to_days >= -28){
            return 0;
        } else if (x.valid_to_days <= 7){
            return 1;
        } else if (x.valid_to_days <=14){
            return 2;
        } else if (x.valid_to_days <= 21){
            return 3;
        } else {
            return 4;
        }
    },

    /**
     * Projects watch_hostports to the array from the collection of certificates.
     * @param certSet
     * @returns {Array}
     */
    getCertHostPorts(certSet){
        return _.sortedUniq(_.sortBy(_.reduce(_.castArray(certSet), (acc, x) => {
            return _.concat(acc, x.watch_hostports);
        }, [])));
    },

    /**
     * Determines certificate issuer from the certificate.
     * @param cert
     * @returns {*}
     */
    certIssuer(cert){
        return Req.certIssuer(cert);
    },

    //
    // Graphs
    //

    /**
     * [[dataset names], [label, d1, d2, ...], [label, d1, d2, ...]]
     * converts to charjs data set format.
     *
     * @param data
     * @returns {*}
     */
    graphDataConv(data){
        if (_.isEmpty(data) || _.isEmpty(data[0])){
            return {};
        }

        const ln = data[0].length;
        const labels = [];
        const datasets = [];
        for(let i=0; i<ln-1; i++){
            datasets.push({
                label: data[0][i+1],
                backgroundColor: this.chartColors[i % this.chartColors.length],
                data: []
            });
        }

        _.forEach(data, (value, idx) => {
            if (idx===0){
                return;
            }
            labels.push(value[0]);
            for(let i=1; i < ln; i++){
                datasets[i-1].data.push(value[i]);
            }
        });
        return {labels: labels, datasets: datasets};
    },

    /**
     * idx to category label
     * @param idx
     * @returns {*}
     */
    getCountCategoryLabel(idx){
        if (idx >= this.countCategories.length){
            return _.last(this.countCategories) + '+';
        }
        return this.countCategories[idx];
    },

    /**
     * idx -> table label
     * @param idx
     * @returns {*}
     */
    getCountCategoryLabelTbl(idx){
        if (idx >= this.countCategories.length){
            return _.last(this.countCategories) + '+';
        } else if ((idx === 0) || (this.countCategories[idx] - this.countCategories[idx-1] < 2)) {
            return this.countCategories[idx]
        }

        return (this.countCategories[idx-1] + 1) + '-' + this.countCategories[idx];
    },

    /**
     * count -> category
     * @param count
     * @returns {number}
     */
    getCountCategory(count){
        let ret = -1;
        const ln = this.countCategories.length;
        for(let idx=0; idx < ln; idx++){
            if (count > this.countCategories[idx]){
                ret = idx;
            } else {
                break;
            }
        }

        return ret+1;
    },

    /**
     * Certificate type aggregation into groups: LE, CDN, Other
     *
     * @param certSet
     * @param cdnCerts optional set of CDN certificate IDs.
     * @returns {number[]}
     */
    certTypes(certSet, cdnCerts){
        const certTypes = [0, 0, 0];  // LE, Cloudflare, Public / other

        for(const [crtIdx, ccrt] of Object.entries(certSet)){
            if (ccrt.is_le){
                certTypes[0] += 1
            } else if (ccrt.is_cloudflare || (cdnCerts && ccrt.id in cdnCerts)){
                certTypes[1] += 1
            } else {
                certTypes[2] += 1
            }
        }
        return certTypes;
    },

    /**
     * Extrapolates certificates over time w.r.t. validity time of the certificates.
     * Used for the certificate planner to account with certificates with short validity period,
     * e.g., LetsEncrypt certificates (3M) - so they are in the 2 year planner multiple times.
     * @param certSet
     * @returns {*}
     */
    extrapolatePlannerCerts(certSet){
        // Adds certificates to the planner multiple times for planner if validity len < 12M
        const valid12m = 3600 * 24 * 365;
        const filtered = _.filter(certSet, x => {
            return x.validity_sec < valid12m;
        });

        if (_.size(filtered) === 0){
            return certSet;
        }

        // Has to clone, we dont want to add extrapolated certificates to other graphs
        const newSet = _.clone(_.castArray(certSet));
        const threshold = moment().utc().add(1, 'year').unix();

        // Add each cert
        _.forEach(filtered, cert => {
            let lastCert = cert;
            while(lastCert.valid_to_utc + lastCert.validity_sec < threshold){
                // create just a lightweight shim, later for full clone do: _.cloneDeep(lastCert);
                const cloned = { is_clone: true };
                cloned.is_le = lastCert.is_le;
                cloned.is_cloudflare = lastCert.is_cloudflare;
                cloned.validity_sec = lastCert.validity_sec;
                cloned.valid_to_utc = lastCert.valid_to_utc + lastCert.validity_sec;
                cloned.valid_from_utc = lastCert.valid_to_utc;
                newSet.push(cloned);
                lastCert = cloned;
            }
        });

        return newSet;
    },

    /**
     * Generates per-month certificate overview.
     * @param certSet
     * @param options
     * @returns {Array}
     */
    monthDataGen(certSet, options){
        options = options || {};
        const cdnCerts = _.get(options, 'cdnCerts');
        const certTypesFnc = _.get(options, 'certTypes', x => {
            return this.certTypes(x, cdnCerts);
        });

        // cert per months, LE, Cloudflare, Others
        const newSet = this.extrapolatePlannerCerts(certSet);
        const grp = _.groupBy(newSet, x => {
            return moment.utc(x.valid_to_utc * 1000.0).format('YYYY-MM');
        });

        const fillGap = (ret, lastMoment, toMoment) => {
            if (_.isUndefined(lastMoment) || lastMoment >= toMoment){
                return;
            }

            const terminal = toMoment.format('MM/YY');
            const i = moment.utc(lastMoment).add(1, 'month');
            for(i; i.format('MM/YY') !== terminal && i < toMoment; i.add(1, 'month')){
                ret.push([ i.format('MM/YY'), 0, 0, 0]);
            }
        };

        const sorted = _.sortBy(grp, [x => {return x[0].valid_to_utc; }]);
        const ret = [];
        let lastGrp = moment().utc().subtract(1, 'month');
        for(const [idx, grp] of Object.entries(sorted)){
            const crt = grp[0];
            const curMoment = moment.utc(crt.valid_to_utc * 1000.0);
            const label = curMoment.format('MM/YY');

            fillGap(ret, lastGrp, curMoment);
            const certTypesStat = certTypesFnc(grp);
            const curEntry = [label, certTypesStat[0], certTypesStat[1], certTypesStat[2]];
            ret.push(curEntry);
            lastGrp = curMoment;
        }

        fillGap(ret, lastGrp, moment().utc().add(1, 'year').add(1, 'month'));
        return ret;
    },

    certDomainsDataGen(certSet, tld){
        const grouped = tld ? this.groupTldDomainsCount(certSet) : this.groupDomainsCount(certSet);
        return _.mapValues(grouped, (cur, key) => {
            const grp = _.castArray(cur);
            return {
                key: key,
                lbl: this.getCountCategoryLabel(key),
                size: _.size(grp),
                distIssuers: _.size(_.groupBy(grp, x => { return x.issuerOrgNorm; })),
                leCnt: _.size(_.filter(grp, x => { return x.is_le; })),
                issuerHist: _.countBy(grp, x => { return x.issuerOrgNorm; }),
                certs: grp
            };
        });
    },

    certIssuersGen(certSet){
        return _.groupBy(certSet, x => {
            return x.issuerOrgNorm;
        });
        //return _.sortBy(grp, [x => {return x[0].issuerOrg; }]);
    },

    groupDomainsCount(certSet){
        return _.groupBy(certSet, x=> {
            return this.getCountCategory(_.size(x.alt_domains));
        });
    },

    groupTldDomainsCount(certSet){
        return _.groupBy(certSet, x=> {
            return this.getCountCategory(_.size(x.alt_slds));
        });
    },

    /**
     * Occurrence / count sorting lambda. User with charts / tables
     * @param x
     * @returns {number}
     */
    invMaxTail(x){
        return -1 * _.max(_.tail(x));
    },

}

