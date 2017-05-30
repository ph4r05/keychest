<template>
    <div class="container">
        <div class="row search">

            <div class="col-sm-8 col-sm-offset-2">
                <form role="form" method="get" id="search-form" @submit.prevent="submitForm()">
                    <!--{{ Laravel.csrfToken }}-->
                    <div class="input-group">
                        <input type="text" class="form-control input-sm" placeholder="Enter your server name"
                               name="scan-target" id="scan-target" >
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
</template>

<script>
    export default {

        data: function() {
            return {
                curUuid: null,
                curJob: null,

            };
        },

        mounted() {
            console.log('Component mounted. x');
            this.$nextTick(function () {
                this.hookup();
            })
        },

        methods: {
            hookup: function(){
//                let sform = $('#search-form');
//                sform.submit(function(e){
//                    console.log('Form submitted x');
//                    this.submitForm();
//                    e.preventDefault(); // avoid to execute the actual submit of the form.
//                });
//                console.log(sform);
            },

            errMsg: function(msg) {
                $('#error-text').val(msg);

                $('#search-info').hide();
                $('#search-error').show();
            },

             searchStarted: function() {
                bodyProgress(true);
                $('#search-form').hide();
                $('#search-info').show();
            },

            pollFinish: function() {
                getJobState(this.curUuid, (function(json){
                    console.log(json);

                    if (json.status !== 'success'){
                        this.errMsg('Job state fail, retry...');
                        setTimeout(this.pollFinish, 1000);
                        return;
                    }

                    this.curJob = json.job;
                    if (this.curJob.state !== 'finished'){
                        setTimeout(this.pollFinish, 1000);
                    } else {
                        this.getResults();
                    }

                }).bind(this), (function(jqxhr, textStatus, error){
                    this.errMsg('Job failed');
                }).bind(this));
            },

            getResults: function() {
                getJobResult(this.curUuid, (function(json){
                    if (json.status !== 'success'){
                        this.errMsg('Job results fail, retry...');
                        setTimeout(this.getResults, 1000);
                        return;
                    }

                    $('#search-info').hide();
                    this.showResults(json);

                }).bind(this), (function(jqxhr, textStatus, error){
                    this.errMsg('Could not get job results');
                }).bind(this));
            },

            showResults: function(json){
                console.log(json);
                $('#search-info').hide();
                $('#search-success').show();
            },

            submitForm: function(){
                let starget = $('#scan-target');
                let domain = starget.val();

                this.searchStarted();
                submitJob(domain, (function(json){
                    bodyProgress(false);
                    if (json.status !== 'success'){
                        this.errMsg('Could not submit the scan');
                        return;
                    }

                    console.log(json);
                    this.curUuid = json.uuid;
                    setTimeout(this.pollFinish, 500);

                }).bind(this), (function(jqxhr, textStatus, error){
                    bodyProgress(false);
                    this.errMsg(error);
                }).bind(this));
            },


//            fetchTaskList: function() {
//                this.$http.get('api/tasks').then(function (response) {
//                    this.list = response.data
//                });
//            },
//
//            createTask: function () {
//                this.$http.post('api/task/store', this.task)
//                this.task.body = ''
//                this.edit = false
//                this.fetchTaskList()
//            },
//
//            updateTask: function(id) {
//                this.$http.patch('api/task/' + id, this.task)
//                this.task.body = ''
//                this.edit = false
//                this.fetchTaskList()
//            },
//
//            showTask: function(id) {
//                this.$http.get('api/task/' + id).then(function(response) {
//                    this.task.id = response.data.id
//                    this.task.body = response.data.body
//                })
//                this.$els.taskinput.focus()
//                this.edit = true
//            },
//
//            deleteTask: function (id) {
//                this.$http.delete('api/task/' + id)
//                this.fetchTaskList()
//            },
        }
    }
</script>
