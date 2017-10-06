/**
 * Created by dusanklinec on 30.05.17.
 */
'use strict';

import _ from 'lodash';
import axios from 'axios';
import moment from 'moment';
import 'moment-timezone';
import Blob from 'w3c-blob';
import URLp from 'url-parse';

/**
 * Returns GET parameter
 * @param parameterName
 * @returns {*}
 */
function findGetParameter(parameterName) {
    let result = null,
        tmp = [];
    const items = location.search.substr(1).split("&");
    for (let index = 0; index < items.length; index++) {
        tmp = items[index].split("=");
        if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
    }
    return result;
}

/**
 * Switches main loading overlay.
 * @param started if true overlay is displayed. Hidden otherwise.
 */
function bodyProgress(started){
    const htmlBody = $("body");
    if (started){
        htmlBody.addClass("loading");
    } else {
        htmlBody.removeClass("loading");
    }
    return true;
}

/**
 * Submit new scan job
 * @param target
 * @param onLoaded
 * @param onFail
 */
function submitJob(target, onLoaded, onFail) {
    const req_data = _.isObjectLike(target) ? target : {'scan-target': target };
    axios.post('/submitJob', req_data)
        .then(response => {
            onLoaded(response.data);
        })
        .catch(e => {
            const err = textStatus + ", " + error;
            console.log("Submit job Request Failed: " + err);
            onFail(e, textStatus, error);
        });
}

/**
 * Performs call on job current state.
 * @param uuid
 * @param onLoaded
 * @param onFail
 */
function getJobState(uuid, onLoaded, onFail) {
    axios.get('/jobState', {params: {'job_uuid': uuid}})
        .then(response => {
            onLoaded(response.data);
        })
        .catch(e => {
            const err = textStatus + ", " + error;
            console.log("Submit job Request Failed: " + err);
            onFail(e, textStatus, error);
        });
}

/**
 * Performs call on job results.
 * @param uuid
 * @param onLoaded
 * @param onFail
 */
function getJobResult(uuid, onLoaded, onFail) {
    axios.get('/jobResult', {params: {'job_uuid': uuid}})
        .then(response => {
            onLoaded(response.data);
        })
        .catch(e => {
            const err = textStatus + ", " + error;
            console.log("Submit job Request Failed: " + err);
            onFail(e, textStatus, error);
        });
}

/**
 * Checks if user is logged in and has timezone filled in. If not, uploads current one.
 */
function doTimezoneCheck(){
    if (!window.Laravel){
        window.setTimeout(doTimezoneCheck, 5000);
        return;
    }

    const laravel = window.Laravel;
    if (!laravel.authUserId || laravel.userTz){
        return;
    }

    const data = {
        'timezone': moment.tz.guess(),
        'utcOffset': moment().utcOffset()
    };

    axios.post('/timezoneSet', data)
        .then(response => {

        })
        .catch(e => {
            console.log(e);
        });
}

/**
 * Basic time zone checking.
 * If not filled in, upload one
 */
function timezoneCheck(){
    window.setTimeout(doTimezoneCheck, 1000);
}

/**
 * Starts keepalive mechanism to keep session alive & detect failure of CSRF token before user does.
 * @param options
 * @param onFail
 */
function pingKeepAlive(options, onFail){
    const onSuccess = (data) => {
        // TODO: reschedule
    };

    const onIfail = (response, e) => {
        if (e){
            onFail(e);
        }
    };

    axios.post('/ping', {'ping': true})
        .then(response => {
            if (!response || !response.data) {
                onFail(response); // no data -> wrong
            } else if (response.data['status'] === 'success') {
                onSuccess(response.data);
            } else {
                onIfail(response);
            }
        })
        .catch(e => {
            if (e && e.response && e.response.status >= 500){
                onIfail(undefined, e);  // CSRF / server error
            } else {

            }
        });
}

/**
 * Default value
 * @param val
 * @param def
 * @returns {*}
 */
function defval(val, def){
    return val ? val : def;
}

/**
 * Returns true if is undefined / empty
 * @param x
 * @returns {boolean}
 */
function isEmpty(x){
    return _.isUndefined(x) || _.isEmpty(x);
}

/**
 * Simple scheme / port filler one form another, http, https supported only.
 * @param scheme
 * @param port
 * @returns {*}
 */
function autoFillSchemePort(scheme, port){
    if (!isEmpty(scheme) && !isEmpty(port)){
        return [scheme, port];
    }

    if (isEmpty(scheme) && isEmpty(port)){
        return ['https', 443];
    }

    if (isEmpty(scheme)){
        if (port === 80){
            return ['http', 80];
        } else if (port === 443){
            return ['https', 443];
        }

    } else {
        if (scheme === 'http'){
            return ['http', 80];
        } else if (scheme === 'https'){
            return ['https', 443];
        }
    }

    return [scheme, port];
}

/**
 * Removes trailing colon from protocol
 * @param x
 */
function protocolFixTrailingColon(x){
    if (isEmpty(x)){
        return x;
    }

    return x.endsWith(':') ? x.substring(0, x.length-1) : x;
}

/**
 * Simple URL parser, adds default scheme if not present to fix the parsing
 * @param url
 * @param defaultScheme
 * @returns {*}
 */
function parseUrl(url, defaultScheme='https'){
    if (isEmpty(url)){
        return url;
    }

    if (!url.match(/^([a-zA-Z0-9]+):\/\//)){
        url = defaultScheme + '://' + url;
    }

    return URLp(url, true);
}

/**
 * Adds default scheme & port
 * @param url
 * @param defaultScheme
 * @param defaultPort
 */
function normalizeUrl(url, defaultScheme='https', defaultPort=443){
    if (isEmpty(url)){
        return url;
    }

    if (!url.match(/^([a-zA-Z0-9]+):\/\//)){
        url = defaultScheme + '://' + url;
    }

    const urlp = URLp(url, true);
    const comps = autoFillSchemePort(protocolFixTrailingColon(urlp.protocol), urlp.port);
    return comps[0] + '://' + urlp.host + comps[1];
}

/**
 * Assembles url from components
 * @param scheme
 * @param host
 * @param port
 * @param defaultScheme
 */
function buildUrl(scheme, host, port, defaultScheme='https'){
    if (isEmpty(scheme)){
        scheme = defaultScheme;
    }

    let ret = scheme + '://' + host;
    return !port ? ret : ret + ':' + port;
}

/**
 * Compares URL in the context of verification.
 * @param schemeA
 * @param hostA
 * @param portA
 * @param schemeB
 * @param hostB
 * @param portB
 */
function isSameUrl(schemeA, hostA, portA, schemeB, hostB, portB){
    if (hostA !== hostB){
        return false;
    }

    const a = autoFillSchemePort(schemeA, portA);
    const b = autoFillSchemePort(schemeB, portB);
    return _.isEqual(a, b);
}

/**
 * Removes wildcard if domain starts on wildcard
 * @param domain
 */
function removeWildcard(domain){
    if (isEmpty(domain)){
        return domain;
    }
    if (_.startsWith(domain, '*.')){
        return domain.substring(2);
    }

    return domain;
}

/**
 * More comprehensive wildcard removal
 * @param domain
 */
function removeAllWildcards(domain){
    if (isEmpty(domain) || !_.isString(domain)){
        return domain;
    }

    domain = domain.replace(/^[*%]\./i, '');
    domain = domain.replace(/^[*%]/i, '');
    return domain;
}

/**
 * Returns true if domain is wildcard
 * @param domain
 * @returns {boolean}
 */
function isWildcard(domain){
    if (isEmpty(domain)){
        return false;
    }

    return _.startsWith(domain, '*.');
}

/**
 * Removes cloudflaressl domain, removes wildcard domains, sort domains.
 * @param domainList
 */
function neighbourDomainList(domainList){
    let domains = [];
    if (isEmpty(domainList)){
        return domains;
    }

    for(const domain of domainList){
        let pureDomain = removeWildcard(domain);
        if (_.endsWith(pureDomain, 'cloudflaressl.com')){
            continue;
        }

        if (pureDomain.match(/sni[0-9a-fA-F]+\.cloudflaressl\.com/g)){
            continue;
        }

        domains.push(pureDomain);
    }

    return _.sortedUniq(domains.sort());
}

/**
 * Compares values to sort them in ascending order.
 * Borrowed from Lodash internal context - not exported.
 *
 * @param {*} value The value to compare.
 * @param {*} other The other value to compare.
 * @returns {number} Returns the sort order indicator for `value`.
 */
function compareAscending(value, other) {
    if (value !== other) {
        const valIsDefined = value !== undefined,
            valIsNull = value === null,
            valIsReflexive = value === value,
            valIsSymbol = _.isSymbol(value);

        const othIsDefined = other !== undefined,
            othIsNull = other === null,
            othIsReflexive = other === other,
            othIsSymbol = _.isSymbol(other);

        if ((!othIsNull && !othIsSymbol && !valIsSymbol && value > other) ||
            (valIsSymbol && othIsDefined && othIsReflexive && !othIsNull && !othIsSymbol) ||
            (valIsNull && othIsDefined && othIsReflexive) ||
            (!valIsDefined && othIsReflexive) ||
            !valIsReflexive) {
            return 1;
        }
        if ((!valIsNull && !valIsSymbol && !othIsSymbol && value < other) ||
            (othIsSymbol && valIsDefined && valIsReflexive && !valIsNull && !valIsSymbol) ||
            (othIsNull && valIsDefined && valIsReflexive) ||
            (!othIsDefined && valIsReflexive) ||
            !othIsReflexive) {
            return -1;
        }
    }
    return 0;
}

/**
 * Converts key function to the compare function
 * @param keyFnc
 */
function keyToCompare(keyFnc){
    return (a, b) => {
        return compareAscending(keyFnc(a), keyFnc(b));
    };
}

/**
 * Converts list of keys to the object with keys
 * [a,b,c] -> {a: true, b: true, c: true}
 * @param lst
 * @returns {{}}
 */
function listToSet(lst){
    const st = {};
    for(const idx in lst){
        st[lst[idx]] = true;
    }
    return st;
}

/**
 * Normalizes a field in the collection col to the common most frequent value
 * collapsing function removes character defined by [^a-zA-Z0-9], then normalizes the groups.
 * adds a new field with the normalized value
 * @param col
 * @param field
 * @param options
 * @returns {Array}
 */
function normalizeValue(col, field, options){
    options = options || {};

    const newField = _.head(_.compact([
        _.isString(options) ? options : null,
        _.isObjectLike(options) && _.has(options, 'newField') ? options['newField'] : null,
        _.isString(field) ? (field + '_new') : field
    ]));

    const normalizer = _.isObjectLike(options) && _.has(options, 'normalizer') && _.isFunction(options['normalizer']) ?
        options['normalizer'] : capitalizeFirstWord;

    const vals = _.map(col, field);

    // group by normalized stripped form of the field
    const grps = _.groupBy(vals, x=>{
        return _.lowerCase(_.replace(x, /[^a-zA-Z0-9]/g, ''));
    });

    // find the representative in the group - group by on subgroups, most frequent subgroup wins
    const subg = _.mapValues(grps, x => {
        return _.groupBy(x);
    });

    // map back all variants of the field to the normalized key - used for normalization
    const normMap = {};
    _.forEach(subg, (val, key) => {
        _.forEach(_.keys(val), x => {
            normMap[x] = key;
        })
    });

    // mapped -> representant
    const repr = _.mapValues(subg, x => {
        if (_.size(x) === 1){
            return _.keys(x)[0];
        }
        return _.reduce(x, (acc, val, key) => {
            return _.size(x[key]) > _.size(x[acc]) ? key : acc;
        });
    });

    // a bit of polishing of the representants
    const frep = _.mapValues(repr, normalizer);

    // normalization step -> add a new field
    return _.map(col, x => {
        const curField = _.isFunction(field) ? field(x) : x[field];
        x[newField] = frep[normMap[curField]];
        return x;
    });
}

/**
 * Capitalizes first word if is all in the same case
 * TERENA -> Terena
 * terena -> Terena
 * cloudFlare -> cloudFlare
 * @param str
 * @returns {string}
 */
function capitalizeFirstWord(str){
    const r = _.replace(str, /^[A-Z]+\b/, _.capitalize);
    return _.replace(r, /^[a-z]+\b/, _.capitalize);
}

/**
 * Take from the list of the given length modulo - cyclic take.
 * @param set
 * @param len
 * @returns {Array}
 */
function takeMod(set, len){
    const ret = [];
    const ln = set.length;
    for(let i = 0; i<len; i++){
        ret.push(set[i % ln]);
    }
    return ret;
}

//
// Certificate functions
//

/**
 * Normalizes certificate issuer
 * @param str
 */
function normalizeIssuer(str){
    if (isEmpty(str)){
        return null;
    }

    let n = capitalizeFirstWord(str);
    n = _.replace(n, /[iI]nc$/, 'Inc.');
    n = _.replace(n, /([a-zA-Z0-9]) Inc\.$/, (m, p1) => {
        return p1 + ', Inc.';
    });
    return n;
}

/**
 * Extracts certificate issuer
 * @param cert
 * @returns {*}
 */
function certIssuer(cert) {
    if (cert.is_le) {
        return 'Let\'s Encrypt';
    } else if (cert.is_cloudflare) {
        return 'Cloudflare';
    }

    const iss = cert.issuer;
    const ret = iss ? iss.match(/organizationName: (.+?)($|,\s[a-zA-Z0-9]+:)/) : null;
    if (ret && ret[1]) {
        return ret[1];
    }

    return 'Other';
}

/**
 * Converts Vue sort order definition to the order by
 * @param sortObj
 * @returns {[*,*]}
 */
function vueSortToOrderBy(sortObj){
    return [
        _.map(sortObj, x=>{ return x.sortField; }),
        _.map(sortObj, x=>{ return x.direction; })
    ]
}

/**
 * Sorts given data according to the vuetable sort string specification.
 * Used with custom vuetable data manager.
 * @param data
 * @param sort
 * @returns {Array}
 */
function vueOrderBy(data, sort){
    const ordering = Req.vueSortToOrderBy(sort);
    return _.orderBy(data, ordering[0], ordering[1]);
}

/**
 * Paginate data for vuetable according to the pagination info, updates pagination itself.
 * Used with custom vuetable data manager.
 * @param data
 * @param pagination
 * @returns {[*,*]}
 */
function vuePagination(data, pagination){
    pagination.total = _.size(data);
    data = _.chunk(data, pagination.per_page)[pagination.current_page - 1];

    pagination.last_page = Math.ceil(pagination.total / pagination.per_page);
    pagination.to = _.min([pagination.from + pagination.per_page - 1, pagination.total]);
    return [data, pagination];
}

/**
 * The download function takes a CSV string, the filename and mimeType as parameters
 * Scroll/look down at the bottom of this snippet to see how download is called
 * @param content
 * @param fileName
 * @param mimeType
 */
function download(content, fileName, mimeType) {
    const a = document.createElement('a');
    mimeType = mimeType || 'application/octet-stream';

    if (navigator.msSaveBlob) { // IE10
        navigator.msSaveBlob(new Blob([content], {
            type: mimeType
        }), fileName);

    } else if ('URL' in window && 'download' in a) { //html5 A[download]
        const myURLcreate = window.URL.createObjectURL || window.webkitURL.createObjectURL;
        a.href = myURLcreate(new Blob([content], {
            type: mimeType
        }));
        a.setAttribute('download', fileName);
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);

    } else {
        location.href = 'data:application/octet-stream,' + encodeURIComponent(content); // only this mime type is supported
    }
}

/**
 * Returns clean Axios instance with X-CSRF-TOKEN and similar which
 * are problematic for accessing CORS API of foreign services, e.g., GitHub.
 * @param options
 */
function apiAxios(options){
    const axos = axios.create(options);
    delete axos.defaults.headers.common['X-Socket-ID'];
    delete axos.defaults.headers.common['X-CSRF-TOKEN'];
    delete axos.defaults.headers.common['X-Requested-With'];
    return axos;
}

//
// Hacks & minor functions
//
function switchTab(tabId){
    $('.nav-tabs a[href="#' + tabId + '"]').tab('show');
}
function switchTabPath(path){
    $(path).tab('show');
}

//
// Export
//
export default {
    bodyProgress: bodyProgress,
    findGetParameter: findGetParameter,
    submitJob: submitJob,
    getJobState: getJobState,
    getJobResult: getJobResult,
    timezoneCheck: timezoneCheck,

    defval: defval,
    isEmpty: isEmpty,
    isSameUrl: isSameUrl,
    autoFillSchemePort: autoFillSchemePort,
    parseUrl: parseUrl,
    normalizeUrl: normalizeUrl,
    buildUrl: buildUrl,
    isWildcard: isWildcard,
    removeWildcard: removeWildcard,
    removeAllWildcards: removeAllWildcards,
    neighbourDomainList: neighbourDomainList,
    compareAscending: compareAscending,
    keyToCompare: keyToCompare,
    normalizeValue: normalizeValue,
    capitalizeFirstWord: capitalizeFirstWord,
    takeMod: takeMod,

    certIssuer: certIssuer,
    normalizeIssuer: normalizeIssuer,
    vueSortToOrderBy: vueSortToOrderBy,
    vueOrderBy: vueOrderBy,
    vuePagination: vuePagination,
    download: download,
    apiAxios: apiAxios,

    switchTab: switchTab,
    switchTabPath: switchTabPath,
};

