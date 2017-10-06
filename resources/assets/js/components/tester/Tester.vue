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
                                <form @submit.prevent="keyTextCheck()" data-vv-scope="keyText">
                                <div class="form-group">
                                    <p>
                                        Paste your <strong>public key</strong> to the field bellow
                                    </p>

                                    <textarea rows="10"
                                              placeholder="public key in text form"
                                              class="form-control"
                                              v-model="keyText"
                                              name="keyText"
                                              data-vv-as="Key"
                                              v-validate="{required: true}"
                                    ></textarea>

                                    <i v-show="errors.has('keyText.keyText')" class="fa fa-warning"></i>
                                    <span v-show="errors.has('keyText.keyText')" class="help is-danger"
                                    >{{ errors.first('keyText.keyText') }}</span>

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
                                            :disabled="errors.has('keyText.keyText') || isRequestInProgress"
                                    >Test the key</button>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div id="tab_2" class="tab-pane">
                        <div class="subdomains-wrapper">
                            <h3>File upload</h3>
                            <form @submit.prevent="keyFileCheck()" data-vv-scope="keyFile">
                            <div class="form-group">
                                <p>
                                    Upload your public key with the form below.
                                </p>
                                <input type="file" placeholder="public key in text form" class="form-control"
                                       @change="onFileChange"
                                       name="keyFile"
                                       data-vv-as="Key File"
                                       v-validate="{size: 10000, required: true}"/>

                                <i v-show="errors.has('keyFile.keyFile')" class="fa fa-warning"></i>
                                <span v-show="errors.has('keyFile.keyFile')" class="help is-danger"
                                >{{ errors.first('keyFile.keyFile') }}</span>

                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-block btn-success"
                                        :disabled="errors.has('keyFile.keyFile') || isRequestInProgress"
                                >Test the key</button>
                            </div>
                            </form>
                        </div>
                    </div>

                    <div id="tab_3" class="tab-pane">
                        <div class="server-import"><h3>URL</h3>
                            <form @submit.prevent="urlCheck()" data-vv-scope="url">
                            <div class="form-group">
                                <p>
                                    Check your HTTPS certificate by entering the domain name below:
                                </p>

                                <input placeholder="URL to check" class="form-control" v-model="url"/>

                                <i v-show="errors.has('url.url')" class="fa fa-warning"></i>
                                <span v-show="errors.has('url.url')" class="help is-danger"
                                >{{ errors.first('url.url') }}</span>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-block btn-success"
                                        :disabled="errors.has('url.url') || isRequestInProgress"
                                >Test the key</button>
                            </div>
                            </form>
                        </div>
                    </div>

                    <div id="tab_4" class="tab-pane">
                        <div class="subdomains-wrapper">
                            <h3>GitHub account</h3>
                            <form @submit.prevent="githubCheck()" data-vv-scope="github">
                            <div class="form-group">
                                <p>
                                    Check your SSH keys used for your GitHub account. Enter your GitHub login name:
                                </p>

                                <input placeholder="GitHub login name" class="form-control"
                                       name="githubNick"
                                       v-model="githubNick"
                                       data-vv-as="GitHub login"
                                       v-validate="{regex: /^[a-zA-Z0-9_]+$/, max: 32, required: true}" />

                                <i v-show="errors.has('github.githubNick')" class="fa fa-warning"></i>
                                <span v-show="errors.has('github.githubNick')" class="help is-danger"
                                >{{ errors.first('github.githubNick') }}</span>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-block btn-success"
                                        :disabled="errors.has('github.githubNick') || isRequestInProgress"
                                >Test the key</button>
                            </div>
                            </form>
                        </div>
                    </div>

                    <div id="tab_5" class="tab-pane">
                        <div class="subdomains-wrapper">
                            <h3>PGP</h3>
                            <form @submit.prevent="pgpCheck()" data-vv-scope="pgp">
                            <div class="form-group">
                                <p>
                                    Check your PGP key by entering either <strong>email</strong> address or <strong>key ID</strong>.
                                </p>

                                <input placeholder="email / key ID" class="form-control" v-model="pgpSearch"
                                       name="pgp"
                                       v-validate="{max: 128, required: true}"
                                       data-vv-as="PGP search query"
                                />

                                <i v-show="errors.has('pgp.pgp')" class="fa fa-warning"></i>
                                <span v-show="errors.has('pgp.pgp')" class="help is-danger"
                                >{{ errors.first('pgp.pgp') }}</span>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-block btn-success"
                                        :disabled="errors.has('pgp.pgp') || isRequestInProgress"
                                >Test the key</button>
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
                                <h2 class="h2-nomarg"><a href="mailto:test@keychest.net" class="a-nounder">test@keychest.net</a></h2>
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

                sendingState: 0,

            }
        },

        mounted() {
            this.$nextTick(() => {
                this.hookup();
            })
        },

        computed: {
            isRequestInProgress(){
                return this.sendingState === 1;
            }
        },

        methods: {
            hookup(){
                
            },

            onStartSending(){
                this.sendingState = 1;
            },

            onSendingFail(){
                this.sendingState = -1;
            },

            onSendFinished(){
                this.sendingState = 2;
            },

            keyTextCheck(){
                const onValid = () => {
                    this.onStartSending();
                    Req.bodyProgress(true);

                    axios.post('/tester/key', {key: this.keyText})
                        .then(res => {
                            // TODO: process
                            console.log(res);
                            this.onSendFinished();
                            Req.bodyProgress(false);
                        })
                        .catch(err => {
                            console.warn(err);
                            this.onSendingFail();
                            Req.bodyProgress(false);
                        });
                };

                // Validate and submit
                this.$validator.validateAll('keyText')
                    .then((result) => {
                        if (result) {
                            onValid();
                            return;
                        }

                        toastr.error('Invalid Key entered', 'Check failed', {
                            timeOut: 2000, preventDuplicates: true
                        });
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
                const data = new FormData();
                data.append('file', this.keyFile);

                const config = {
                    onUploadProgress: (progressEvent) => {
                        const percentCompleted = Math.round( (progressEvent.loaded * 100) / progressEvent.total );
                        console.log(percentCompleted);
                    }
                };

                const onValid = () => {
                    this.onStartSending();
                    Req.bodyProgress(true);

                    axios.put('/tester/file', data, config)
                        .then(res => {
                            this.onSendFinished();
                            Req.bodyProgress(false);
                            console.log(res);
                        })
                        .catch(err => {
                            this.onSendFinished();
                            Req.bodyProgress(false);
                            console.warn(err);
                        });
                };

                // Validate and submit
                this.$validator.validateAll('keyFile')
                    .then((result) => {
                        if (result) {
                            onValid();
                            return;
                        }

                        toastr.error('Invalid Key File', 'Check failed', {
                            timeOut: 2000, preventDuplicates: true
                        });
                    });
            },

            urlCheck(){
                // TODO: spot-check like
            },

            githubCheck(){

                const onValid = () => {
                    const axos = Req.apiAxios();

                    this.onStartSending();
                    Req.bodyProgress(true);

                    // Get github ssh keys first.
                    axos.get('https://api.github.com/users/' + this.githubNick + '/keys')
                        .then(response => {
                            this.githubCheckKeys(response.data);
                        })
                        .then(response => {
                            this.onSendFinished();
                            Req.bodyProgress(false);
                            console.log('save-resp');
                            console.log(response);
                        })
                        .catch(e => {
                            console.warn(e);
                            this.onSendingFail();
                            Req.bodyProgress(false);
                            toastr.error('Could not find given account name', 'Check failed', {
                                timeOut: 2000, preventDuplicates: true
                            });
                        });
                };

                // Validate and submit
                this.$validator.validateAll('github')
                    .then((result) => {
                    if (result) {
                        onValid();
                        return;
                    }

                    toastr.error('Invalid GitHub login name', 'Check failed', {
                        timeOut: 2000, preventDuplicates: true
                    });
                });
            },

            githubCheckKeys(res){
                return new Promise( (resolve, reject) => {
                    axios.post('/tester/key', {keys: res, keyType: 'github'})
                        .then(function (res) {
                            resolve(res);
                        })
                        .catch(function (err) {
                            reject(err);
                        });
                });
            },

            pgpCheck(){
                // TODO: submit check
                const onValid = () => {
                    this.onStartSending();
                    Req.bodyProgress(true);

                    axios.get('/tester/pgp', {params: {pgp: this.pgpSearch}})
                        .then(res => {
                            console.log(res);
                            this.onSendFinished();
                            Req.bodyProgress(false);
                        })
                        .catch(err => {
                            console.warn(err);
                            this.onSendingFail();
                            Req.bodyProgress(false);
                        });
                };

                // Validate and submit
                this.$validator.validateAll('pgp')
                    .then((result) => {
                        if (result) {
                            onValid();
                            return;
                        }

                        toastr.error('Invalid PGP search query', 'Check failed', {
                            timeOut: 2000, preventDuplicates: true
                        });
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

    .a-nounder {
        text-decoration: none;
    }
</style>