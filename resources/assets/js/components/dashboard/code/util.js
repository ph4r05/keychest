import _ from 'lodash';
import moment from 'moment';

import Psl from 'ph4-psl';
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

    /**
     * Warms up PSL cache by loading several entries.
     * @param psl
     */
    pslWarmUp(psl){
        psl = psl || Psl;
        psl.get('test.now.sh');
        psl.get('test.通販');
        return psl;
    },

    /**
     * Formats UTC time with toLocaleDateString.
     * @param utc
     * @param locale
     * @param params
     * @returns {string}
     */
    utcTimeLocaleDateString(utc, locale, params){
        return (new Date(utc * 1000.0)).toLocaleDateString(locale, params);
    },

    /**
     * Formats UTC time with toLocaleDateString.
     * @param utc
     * @param locale
     * @param params
     * @returns {string}
     */
    utcTimeLocaleDateStringUs(utc, locale, params){
        return (new Date(utc * 1000.0)).toLocaleDateString(locale || 'en-us', params || this.dateUsParams());
    },

    /**
     * Formats UTC time with toLocaleString.
     * @param utc
     * @param locale
     * @param params
     * @returns {string}
     */
    utcTimeLocaleStringUs(utc, locale, params){
        return (new Date(utc * 1000.0)).toLocaleString(locale || 'en-us', params || this.dateUsParams());
    },

    /**
     * Formats UTC time with toLocaleString.
     * @param utc
     * @param locale
     * @param params
     * @returns {string}
     */
    utcTimeLocaleString(utc, locale, params){
        return (new Date(utc * 1000.0)).toLocaleString(locale, params);
    },

    /**
     * Formats given date with toLocaleString, under given locale and with given params.
     * @param {Date} date
     * @param locale
     * @param params
     * @returns {string}
     */
    dateLocaleString(date, locale, params){
        return date.toLocaleString(locale, params);
    },

    /**
     * Formats given date with toLocaleString, under given locale (en-us by default)
     * and with given params (dateUsParams by default)
     * @param {Date} date
     * @param locale
     * @param params
     * @returns {string}
     */
    dateLocaleStringParams(date, locale, params){
        return date.toLocaleString(locale || 'en-us', params || this.dateUsParams());
    },

    /**
     * Returns current date in the US format.
     * @returns {string}
     */
    curDateUsString(){
        return (new Date()).toLocaleString('en-us', this.dateUsParams());
    },

    /**
     * Returns params for Date.toLocaleString()
     * @returns {{day: string, month: string, year: string, hour: string, minute: string}}
     */
    dateUsParams(){
        return {
            'day': 'numeric',
            'month': 'short',
            'year': 'numeric',
            'hour': 'numeric',
            'minute': 'numeric'
        };
    },

    //
    // Datasets
    //

    tlsErrors(tlsScans){
        return _.sortBy(_.filter(tlsScans, x => {
                return x && x.status !== 1;
            }),
            [
                x => { return x.url_short; },
                x => { return x.ip_scanned; }
            ]);
    },

    /**
     * Dataset of certificates with imminent renewal
     * @param certs
     * @returns {*|Array}
     */
    imminentRenewalCerts(certs){
        const imm = _.filter(certs, x => { return (x.valid_to_days <= 28 && x.valid_to_days >= -28) });
        const grp = _.groupBy(imm, x => {
            return x.valid_to_dayfmt;
        });
        return _.sortBy(grp, [x => {return x[0].valid_to_days; }]);
    },

    /**
     * Produces CDN mapping - set of certificate IDs recognized as CDN owned (e.g., Cloudflare)
     * @param tlsScans
     * @param certificates
     * @returns {{}}
     */
    cdnCerts(tlsScans, certificates){
        const cdnCertsTls = _.map(_.filter(_.values(tlsScans), tls => {
            return !_.isEmpty(tls.cdn_cname) || !_.isEmpty(tls.cdn_headers) || !_.isEmpty(tls.cdn_reverse);
        }), tls => {
            return tls.cert_id_leaf;
        });

        return Req.listToSet(_.uniq(_.union(cdnCertsTls,
            _.map(_.filter(certificates, crt =>{
                return crt.is_cloudflare;
            }), crt => {
                return crt.id;
            })
        )));
    },

    /**
     * Generates dataset for week4 renewal
     * @param dataset
     * @returns {*|Array}
     */
    week4renewals(dataset){
        const r = _.filter(dataset, x => {
            return x && x.valid_to_days && x.valid_to_days <= 28;
        });
        const r2 = _.map(r, x => {
            x.week4cat = this.week4grouper(x);
            return x;
        });
        const grp = _.groupBy(r2, x => {
            return x.week4cat;
        });
        return _.sortBy(grp, [x => {return x[0].valid_to_days; }]);
    },

    /**
     * Week4 renewal counts dataset
     * @param dataset
     * @returns {number[]}
     */
    week4renewalsCounts(dataset){
        const r = _.filter(dataset, x => {
            return x && x.valid_to_days && x.valid_to_days <= 28 && x.valid_to_days >= -28;
        });
        const ret = [0, 0, 0, 0, 0];
        _.forEach(r, x => {
            ret[this.week4grouper(x)] += 1;
        });
        return ret;
    },

    //
    // Cert utils
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
    // Result processing
    //

    /**
     * Processes loaded dashboard results.
     * @param results
     */
    processResults(results){
        this.processWatchResults(results);
        this.processCertificatesResults(results);
        this.processWhoisResults(results);
        this.processDnsResults(results);
        this.processTlsResults(results);
    },

    /**
     * Processes Watches part of the loaded results.
     * @param results
     */
    processWatchResults(results){
        if (!results.watches){
            return;
        }

        // noinspection JSUnusedLocalSymbols
        for(const [watch_id, watch] of Object.entries(results.watches)){
            this.extendDateField(watch, 'last_scan_at');
            this.extendDateField(watch, 'created_at');
            this.extendDateField(watch, 'updated_at');
        }
    },

    /**
     * Processes Certificates part of the loaded results.
     * @param results
     */
    processCertificatesResults(results){
        const curTime = moment().valueOf() / 1000.0;
        const fqdnResolver = _.memoize(Psl.get);
        const wildcardRemover = _.memoize(Req.removeWildcard);

        for(const [certId, cert] of Object.entries(results.certificates)){
            cert.valid_to_dayfmt = moment.utc(cert.valid_to_utc * 1000.0).format('YYYY-MM-DD');
            cert.valid_to_days = Math.round(10 * (cert.valid_to_utc - curTime) / 3600.0 / 24.0) / 10;
            cert.valid_from_days = Math.round(10 * (curTime - cert.valid_from_utc) / 3600.0 / 24.0) / 10;
            cert.validity_sec = cert.valid_to_utc - cert.valid_from_utc;
            cert.watch_hosts = [];
            cert.watch_hostports = [];
            cert.watch_urls = [];
            cert.watch_hosts_ct = [];
            cert.watch_urls_ct = [];
            cert.alt_domains = _.sortedUniq(_.sortBy(_.map(_.castArray(cert.alt_names), x => {
                return wildcardRemover(x);
            })));
            cert.alt_slds = _.sortedUniq(_.sortBy(_.map(_.castArray(cert.alt_domains), x => {
                return fqdnResolver(x);  // too expensive now. 10 seconds for 150 certs. invoke later
            })));

            _.forEach(cert.tls_watches_ids, watch_id => {
                if (watch_id in results.watches){
                    cert.watch_hostports.push(results.watches[watch_id].host_port);
                    cert.watch_hosts.push(results.watches[watch_id].scan_host);
                    cert.watch_urls.push(results.watches[watch_id].url);
                }
            });

            _.forEach(
                _.uniq(_.union(
                    cert.tls_watches_ids,
                    cert.crtsh_watches_ids)), watch_id =>
                {
                    if (watch_id in results.watches) {
                        cert.watch_hosts_ct.push(results.watches[watch_id].scan_host);
                        cert.watch_urls_ct.push(results.watches[watch_id].url);
                    }
                });

            cert.watch_hostports = _.sortedUniq(cert.watch_hostports.sort());
            cert.watch_hosts = _.sortedUniq(cert.watch_hosts.sort());
            cert.watch_urls = _.sortedUniq(cert.watch_urls.sort());
            cert.watch_hosts_ct = _.sortedUniq(cert.watch_hosts_ct.sort());
            cert.watch_urls_ct = _.sortedUniq(cert.watch_urls_ct.sort());
            cert.last_scan_at_utc = _.reduce(cert.tls_watches_ids, (acc, val) => {
                if (!results.watches || !(val in results.watches)){
                    return acc;
                }
                const sc = results.watches[val].last_scan_at_utc;
                return sc >= acc ? sc : acc;
            }, null);

            cert.planCss = {tbl: {
                    'success': cert.valid_to_days > 14 && cert.valid_to_days <= 28,
                    'warning': cert.valid_to_days > 7 && cert.valid_to_days <= 14,
                    'warning-hi': cert.valid_to_days > 0  && cert.valid_to_days <= 7,
                    'danger': cert.valid_to_days <= 0,
                }};

            if (cert.is_le) {
                cert.type = 'Let\'s Encrypt';
            } else if (cert.is_cloudflare){
                cert.type = 'Cloudflare';
            } else {
                cert.type = 'Public';
            }

            cert.issuerOrg = this.certIssuer(cert);
        }

        Req.normalizeValue(results.certificates, 'issuerOrg', {
            newField: 'issuerOrgNorm',
            normalizer: Req.normalizeIssuer
        });
    },

    /**
     * Processes Whois part of the loaded results
     * @param results
     */
    processWhoisResults(results){
        if (!results.whois){
            return;
        }

        for(const [whois_id, whois] of Object.entries(results.whois)){
            this.extendDateField(whois, 'expires_at');
            this.extendDateField(whois, 'registered_at');
            this.extendDateField(whois, 'rec_updated_at');
            this.extendDateField(whois, 'last_scan_at');
            whois.planCss = {
                tbl: {
                    'success': whois.expires_at_days > 3 * 28 && whois.expires_at_days <= 6 * 28,
                    'warning': whois.expires_at_days > 28 && whois.expires_at_days <= 3 * 28,
                    'warning-hi': whois.expires_at_days > 14 && whois.expires_at_days <= 28,
                    'danger': whois.expires_at_days <= 14,
                }
            };
        }
    },

    /**
     * Processes DNS part of the loaded results.
     * @param results
     */
    processDnsResults(results){
        if (!results.dns){
            return;
        }

        for (const [dns_id, dns] of Object.entries(results.dns)) {
            this.extendDateField(dns, 'last_scan_at');
            dns.domain = results.watches && dns.watch_id in results.watches ?
                results.watches[dns.watch_id].scan_host : undefined;
        }
    },

    /**
     * Processes TLS part of the loaded results.
     * @param results
     */
    processTlsResults(results){
        if (!results.tls){
            return;
        }

        for(const [tls_id, tls] of Object.entries(results.tls)){
            this.extendDateField(tls, 'last_scan_at');
            this.extendDateField(tls, 'created_at');
            this.extendDateField(tls, 'updated_at');
            if (results.watches && tls.watch_id in results.watches){
                tls.domain = results.watches[tls.watch_id].scan_host;
                tls.url_short = results.watches[tls.watch_id].url_short;
            }

            tls.leaf_cert = results.certificates
                    && tls.cert_id_leaf
                    && tls.cert_id_leaf in results.certificates ?
                results.certificates[tls.cert_id_leaf] : undefined;

            if (tls.leaf_cert){
                tls.host_cert = tls.leaf_cert;

            } else if (results.certificates && tls.certs_ids && _.size(tls.certs_ids) === 1){
                tls.host_cert = results.certificates[tls.certs_ids[0]];
            }
        }
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
        idx = Number(idx);
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

