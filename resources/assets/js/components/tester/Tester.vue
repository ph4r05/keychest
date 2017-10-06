<template>
    <div class="container-fluid spark-screen">

        <div class="row">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">Text input</a></li>
                    <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">File upload</a></li>
                    <!--<li class=""><a href="#tab_3" data-toggle="tab" aria-expanded="false">URL</a></li>-->
                    <li class=""><a href="#tab_4" data-toggle="tab" aria-expanded="false">GitHub account</a></li>
                    <li class=""><a href="#tab_5" data-toggle="tab" aria-expanded="false">PGP</a></li>
                    <li class=""><a href="#tab_6" data-toggle="tab" aria-expanded="false">Send us an e-mail</a></li>
                    <!--<li class="pull-right"><a href="#" title="Refresh" class="text-muted"><i class="fa fa-refresh"></i></a></li>-->
                </ul>
                <div class="tab-content">
                    <div id="tab_1" class="tab-pane active">
                        <div>
                            <div style=""><h3>Text input</h3>
                                <form>
                                <div class="form-group">
                                    <p>
                                        Paste your <strong>public key</strong> to the field bellow
                                    </p>

                                    <textarea rows="10"
                                              placeholder="public key in text form"
                                              class="form-control"
                                              v-model="keyText"
                                    ></textarea>
                                </div>
                                <div class="form-group">
                                    <p>
                                        Supported formats:
                                    </p>
                                    <ul>
                                        <li>X509 Certificate, PEM encoded</li>
                                        <li>RSA PEM encoded public key</li>
                                        <li>SSH public key</li>
                                        <li>PGP public key</li>
                                        <li>Raw RSA modulus (hex / decimal / base64 encoded)</li>
                                        <li>S/MIME PKCS7 Signature</li>
                                    </ul>

                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-block btn-success"
                                            @click.prevent="keyTextCheck()">Test the key</button>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div id="tab_2" class="tab-pane">
                        <div class="subdomains-wrapper">
                            <h3>File upload</h3>
                            <form>
                            <div class="form-group">
                                <p>
                                    Upload your public key with the form below.
                                </p>
                                <input type="file" placeholder="public key in text form" class="form-control"
                                       @change="onFileChange"
                                       name="keyFile"
                                       v-validate="{size: 10000, required: true}"/>
                                <i v-show="errors.has('keyFile')" class="fa fa-warning"></i>
                                <span v-show="errors.has('keyFile')" class="help is-danger">{{ errors.first('keyFile') }}</span>

                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-block btn-success"
                                        @click.prevent="keyFileCheck()">Test the key</button>
                            </div>
                            </form>
                        </div>
                    </div>

                    <div id="tab_3" class="tab-pane">
                        <div class="server-import"><h3>URL</h3>
                            <form>
                            <div class="form-group">
                                <p>
                                    Check your HTTPS certificate by entering the domain name below:
                                </p>

                                <input placeholder="URL to check" class="form-control" v-model="url"/>
                                <i v-show="errors.has('url')" class="fa fa-warning"></i>
                                <span v-show="errors.has('url')" class="help is-danger">{{ errors.first('url') }}</span>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-block btn-success"
                                        @click.prevent="urlCheck()">Test the key</button>
                            </div>
                            </form>
                        </div>
                    </div>

                    <div id="tab_4" class="tab-pane">
                        <div class="subdomains-wrapper">
                            <h3>GitHub account</h3>
                            <form>
                            <div class="form-group">
                                <p>
                                    Check your SSH keys used for your GitHub account. Enter your GitHub login name:
                                </p>

                                <input placeholder="GitHub login name" class="form-control"
                                       name="githubNick" v-model="githubNick"
                                       v-validate="{regex: /^[a-zA-Z0-9_]+$/, max: 32, required: true}" />

                                <i v-show="errors.has('githubNick')" class="fa fa-warning"></i>
                                <span v-show="errors.has('githubNick')" class="help is-danger">{{ errors.first('githubNick') }}</span>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-block btn-success"
                                        @click.prevent="githubCheck()"
                                >Test the key</button>
                            </div>
                            </form>
                        </div>
                    </div>

                    <div id="tab_5" class="tab-pane">
                        <div class="subdomains-wrapper">
                            <h3>PGP</h3>
                            <form>
                            <div class="form-group">
                                <p>
                                    Check your PGP key by entering either <strong>email</strong> address or <strong>key ID</strong>.
                                </p>

                                <input placeholder="email / key ID" class="form-control" v-model="pgpSearch"
                                       name="pgp"
                                       v-validate="{max: 128, required: true}"
                                />
                                <i v-show="errors.has('pgp')" class="fa fa-warning"></i>
                                <span v-show="errors.has('pgp')" class="help is-danger">{{ errors.first('pgp') }}</span>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-block btn-success"
                                        @click.prevent="pgpCheck()">Test the key</button>
                            </div>
                            </form>
                        </div>
                    </div>

                    <div id="tab_6" class="tab-pane">
                        <div class="subdomains-wrapper">
                            <h3>Send us an e-mail</h3>

                            <p>
                                For easy testing of <strong>PGP</strong> keys and <strong>SMIME</strong> certificates
                                you can send us a signed email to the account below, we will check it and send you report back.
                            </p>

                            <div class="alert alert-info text-center">
                                <h2 class="h2-nomarg"><a href="mailto:test@keychest.net">test@keychest.net</a></h2>
                            </div>

                            <p>
                                <small><strong>Note:</strong> for PGP email its better to attach your public key to
                                    the email, most of the emailing clients supports this.</small>
                            </p>

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
    import VeeValidate from 'vee-validate';
    import { mapFields } from 'vee-validate';

    Vue.use(ToggleButton);
    Vue.use(VeeValidate, {fieldsBagName: 'formFields'});

    VeeValidate.Validator.localize('en', {
        attributes: {
            keyFile: 'Key File',
            githubNick: 'GitHub login',
            pgp: 'PGP search query',
            url: 'URL'
        }
    });

    export default {
        data: function() {
            return {
                keyText: null,
                keyFile: null,
                url: null,
                githubNick: null,
                pgpSearch: null,
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
                
            },

            keyTextCheck(){
                // TODO: submit check
                axios.post('/tester/key', {key: this.keyText})
                    .then(function (res) {
                        console.log(res);
                    })
                    .catch(function (err) {
                        console.warn(err);
                    });
            },

            onFileChange(e){
                const files = e.target.files || e.dataTransfer.files;
                if (!files.length){
                    return;
                }

                this.keyFile = files[0];
            },

            keyFileCheck(){
                console.log(this.keyFile);
                const data = new FormData();
                data.append('file', this.keyFile);

                const config = {
                    onUploadProgress: (progressEvent) => {
                        const percentCompleted = Math.round( (progressEvent.loaded * 100) / progressEvent.total );
                        console.log(percentCompleted);
                    }
                };

                axios.put('/tester/file', data, config)
                    .then(function (res) {
                        console.log(res);
                    })
                    .catch(function (err) {
                        console.warn(err);
                    });

            },

            urlCheck(){
                // TODO: spot-check like
            },

            githubCheck(){
                // TODO: validation working?

                // Fetch github key via API
                const axos = Req.apiAxios();
                Req.bodyProgress(true);
                
                axos.get('https://api.github.com/users/' + this.githubNick + '/keys')
                .then(response => {
                    Req.bodyProgress(false);
                    this.githubCheckKeys(response.data);
                })
                .catch(e => {
                    Req.bodyProgress(false);
                    console.warn(e);
                    toastr.error('Could not find given account name', 'Check failed', {
                        timeOut: 2000, preventDuplicates: true
                    });
                });
            },

            githubCheckKeys(res){
                console.log(res);

                // TODO: submit check
                axios.post('/tester/key', {keys: res, keyType: 'github'})
                    .then(function (res) {
                        console.log(res);
                    })
                    .catch(function (err) {
                        console.warn(err);
                    });
            },

            pgpCheck(){
                // TODO: submit check
                axios.get('/tester/pgp', {params: {pgp: this.pgpSearch}})
                    .then(function (res) {
                        console.log(res);
                    })
                    .catch(function (err) {
                        console.warn(err);
                    });
            }
        },

        events: {

        }
    }
</script>
<style scoped>
    .h2-nomarg {
        margin: 0;
    }
</style>