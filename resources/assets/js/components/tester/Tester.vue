<template>
    <div class="container-fluid spark-screen">
        <div class="row">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">Text input</a></li>
                    <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">File upload</a></li>
                    <li class=""><a href="#tab_3" data-toggle="tab" aria-expanded="false">URL</a></li>
                    <li class=""><a href="#tab_4" data-toggle="tab" aria-expanded="false">GitHub account</a></li>
                    <li class=""><a href="#tab_5" data-toggle="tab" aria-expanded="false">PGP ID</a></li>
                    <li class=""><a href="#tab_6" data-toggle="tab" aria-expanded="false">Send us an e-mail</a></li>
                    <!--<li class="pull-right"><a href="#" title="Refresh" class="text-muted"><i class="fa fa-refresh"></i></a></li>-->
                </ul>
                <div class="tab-content">
                    <div id="tab_1" class="tab-pane active">
                        <div>
                            <div style=""><h3>Text input</h3>
                                <p>Test</p>
                            </div>
                        </div>
                    </div>

                    <div id="tab_2" class="tab-pane">
                        <div class="subdomains-wrapper">
                            <h3>File upload</h3>
                        </div>
                    </div>

                    <div id="tab_3" class="tab-pane">
                        <div class="server-import"><h3>URL</h3>
                            <div class="form-group"><p>This tab allows bulk import of domain names, servers, for
                                monitoring.
                                Paste a list of your servers into the text field below.</p>
                                <p>Format: one server per line, you can specify a port for each server using the
                                    format: "domain_name:port", e.g., <i>keychest.net:11100</i>.</p>
                                <textarea rows="10"
                                     placeholder="a server per line, up to 100 lines"
                                     class="form-control"></textarea>
                                <small>Max 100 lines per import</small>
                            </div>
                            <div class="form-group">
                                <button type="button" class="btn btn-block btn-success">Import</button>
                            </div>
                        </div>
                    </div>

                    <div id="tab_4" class="tab-pane">
                        <div class="subdomains-wrapper">
                            <h3>GitHub account</h3>
                        </div>
                    </div>

                    <div id="tab_5" class="tab-pane">
                        <div class="subdomains-wrapper">
                            <h3>PGP ID</h3>
                        </div>
                    </div>

                    <div id="tab_6" class="tab-pane">
                        <div class="subdomains-wrapper">
                            <h3>Send us an e-mail</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import _ from 'lodash';
    import axios from 'axios';
    import moment from 'moment';
    import sprintf from 'sprintf-js';
    import Req from 'req';
    import ph4 from 'ph4';

    import ToggleButton from 'vue-js-toggle-button';
    import toastr from 'toastr';

    import Vue from 'vue';

    Vue.use(ToggleButton);

    export default {
        data: function() {
            return {
            }
        },

        mounted() {
            this.$nextTick(() => {
                this.hookup();
            })
        },

        computed: {

        },

        methods: {
            hookup(){
                
            }
        },

        events: {

        }
    }
</script>
<style scoped>
</style>