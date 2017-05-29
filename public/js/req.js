/**
 * Created by dusanklinec on 29.05.17.
 */
"use strict";

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
            console.log( "Request Failed: " + err );
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
            console.log( "Request Failed: " + err );
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
            console.log( "Request Failed: " + err );
            onFail(jqxhr, textStatus, error);
        });
}

/**
 * Load stats data, create templte.
 */
function loadStats(){
    loadStatsData(
        function(json){
            // Sort by name
            var vals = $.map(json.users, function(v) { return v; });
            vals.sort(function(a, b){
                if(a.cname == b.cname) return 0;
                return a.cname < b.cname ? -1 : 1
            });

            for(var i=0; i<vals.length; i++) {
                var user = vals[i];
                user.total_day = formatBytesWrap(user.day.recv + user.day.sent);
                user.total_week = formatBytesWrap(user.last7d.recv + user.last7d.sent);
                user.total_month = formatBytesWrap(user.month.recv + user.month.sent);
                user.connected_fmt = '-';
                user.status_style = 'statusOffline';

                if (user.date_connected && user.connected) {
                    var d = new Date(user.date_connected * 1000);
                    user.connected_fmt = formatDate(d);
                    user.status_style = 'statusOnline';
                }
            }

            var html = statsTemplate({users:vals});
            statsPlaceholder.html(html);
            statsWrapper.show();
            setTimeout(loadStats, 10000);
        },
        function(jqxhr, textStatus, error){
            statsWrapper.hide();
            setTimeout(loadStats, 30000);
        }
    );
}

/**
 * Initial stats load
 */
function loadStatsInit(){
    statsSource = $("#connectedTpl").html();
    statsTemplate = Handlebars.compile(statsSource);
    statsWrapper = $("#userStats");
    statsPlaceholder = $("#statsPlaceholder");
    loadStats();
}


