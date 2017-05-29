@extends('layouts.app')

@section('content')
    <div class="container">
    <div class="row search">

        <div class="col-sm-8 col-sm-offset-2">
            <form role="form" method="get" id="search-form">
                {{ csrf_field() }}
                <div class="input-group">
                    <input type="text" class="form-control input-sm" placeholder="Enter your server name"
                           name="scan-target" id="scan-target">
                    <span class="input-group-btn">
                        <button class="btn btn-default btn-sm" type="submit">
                            <span class="glyphicon glyphicon-search"></span>
                        </button>
                    </span>
                </div>
            </form>

            <div class="alert alert-danger" id="search-error" style="display: none">
                <strong>Error!</strong> <span id="error-text"></span>
            </div>

            <div class="alert alert-info" id="search-info" style="display: none">
                <span id="info-text">Waiting for scan to finish...</span>
            </div>

            <div class="alert alert-success" id="search-success" style="display: none">
                <strong>Success!</strong> Scan finished.
            </div>

        </div>

    </div>
    </div>

    <script>
        "use strict";
        var sform = null;
        var starget = null;
        var curUuid = null;
        var curJob = null;

        function errMsg(msg){
            $('#error-text').val(msg);

            $('#search-info').hide();
            $('#search-error').show();
        }

        function searchStarted(){
            bodyProgress(true);
            $('#search-form').hide();
            $('#search-info').show();
        }

        function pollFinish(){
            getJobState(curUuid, function(json){
                console.log(json);

                if (json.status !== 'success'){
                    errMsg('Job state fail, retry...');
                    setTimeout(pollFinish, 1000);
                    return;
                }

                curJob = json.job;
                if (curJob.state !== 'finished'){
                    setTimeout(pollFinish, 1000);
                } else {
                    getResults();
                }

            }, function(jqxhr, textStatus, error){
                errMsg('Job failed');
            });
        }

        function getResults(){
            getJobResult(curUuid, function(json){
                if (json.status !== 'success'){
                    errMsg('Job results fail, retry...');
                    setTimeout(getResults, 1000);
                    return;
                }

                $('#search-info').hide();
                showResults(json);

            }, function(jqxhr, textStatus, error){
               errMsg('Could not get job results');
            });
        }

        function showResults(json){
            console.log(json);
            $('#search-info').hide();
            $('#search-success').show();

        }

        function submitForm(){
            var domain = starget.val();

            searchStarted();
            submitJob(domain, function(json){
                bodyProgress(false);
                if (json.status !== 'success'){
                    errMsg('Could not submit the scan');
                    return;
                }

                console.log(json);
                curUuid = json.uuid;
                setTimeout(pollFinish, 500);

            }, function(jqxhr, textStatus, error){
                bodyProgress(false);
                errMsg(error);
            });
        }

        // Initialized, hook up.
        $( document ).ready(function() {
            sform = $('#search-form');
            starget = $('#scan-target');
            sform.submit(function(e){
                submitForm();
                e.preventDefault(); // avoid to execute the actual submit of the form.
            });
        });
    </script>

@endsection

