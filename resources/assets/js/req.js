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
    console.log('Progress: ' + started);
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

// Export
module.exports = {
    bodyProgress: bodyProgress,
    findGetParameter: findGetParameter,
    submitJob: submitJob,
    getJobState: getJobState,
    getJobResult: getJobResult,
    defval: defval
};



