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



        </div>

    </div>
    </div>

    <script>
        $( document ).ready(function() {
            var sform = $('#search-form');
            var starget = $('#scan-target');
            sform.submit(function(e){
                var domain = starget.val();

                bodyProgress(true);
                submitJob(domain, function(json){
                    bodyProgress(false);
                    console.log(json);

                }, function(jqxhr, textStatus, error){
                    bodyProgress(false);

                });

                e.preventDefault(); // avoid to execute the actual submit of the form.
            });
        });
    </script>

@endsection

