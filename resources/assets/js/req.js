/**
 * Created by dusanklinec on 30.05.17.
 */
'use strict';

/**
 * Returns GET parameter
 * @param parameterName
 * @returns {*}
 */
function findGetParameter(parameterName) {
    var result = null,
        tmp = [];
    var items = location.search.substr(1).split("&");
    for (var index = 0; index < items.length; index++) {
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
    var htmlBody = $("body");
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
function submitJob(target, onLoaded, onFail){
    $.getJSON("/submitJob", {'scan-target': target})
        .done(function( json ) {
            onLoaded(json);
        })
        .fail(function( jqxhr, textStatus, error ) {
            var err = textStatus + ", " + error;
            console.log( "Submit job Request Failed: " + err );
            onFail(jqxhr, textStatus, error);
        });
}

/**
 * Performs call on job current state.
 * @param uuid
 * @param onLoaded
 * @param onFail
 */
function getJobState(uuid, onLoaded, onFail){
    $.getJSON("/jobState", {'job_uuid': uuid})
        .done(function( json ) {
            onLoaded(json);
        })
        .fail(function( jqxhr, textStatus, error ) {
            var err = textStatus + ", " + error;
            console.log( "Get Job state Request Failed: " + err );
            onFail(jqxhr, textStatus, error);
        });
}

/**
 * Performs call on job results.
 * @param uuid
 * @param onLoaded
 * @param onFail
 */
function getJobResult(uuid, onLoaded, onFail){
    $.getJSON("/jobResult", {'job_uuid': uuid})
        .done(function( json ) {
            onLoaded(json);
        })
        .fail(function( jqxhr, textStatus, error ) {
            var err = textStatus + ", " + error;
            console.log( "Get Job result Request Failed: " + err );
            onFail(jqxhr, textStatus, error);
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

    const urlp = URL(url, true);
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
    return isEmpty(port) ? ret : ret + ':' + port;
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


// Export
module.exports = {
    bodyProgress: bodyProgress,
    findGetParameter: findGetParameter,
    submitJob: submitJob,
    getJobState: getJobState,
    getJobResult: getJobResult,
    defval: defval,
    isEmpty: isEmpty,
    isSameUrl: isSameUrl,
    autoFillSchemePort: autoFillSchemePort,
    normalizeUrl: normalizeUrl,
    buildUrl: buildUrl,
    removeWildcard: removeWildcard,
    neighbourDomainList: neighbourDomainList
};



