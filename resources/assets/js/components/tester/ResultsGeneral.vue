<template>
    <transition name="fade">
    <div class="col-md-12" v-if="displayFlag">

        <div class="alert alert-info-2 alert-waiting scan-alert" v-if="isLoading">
            <span>Loading results, please wait...</span>
        </div>
        <div class="alert alert-danger-2 scan-alert" v-else-if="errorFlag">
            <span><strong>Error</strong> during the key processing, please refresh the page and try again.</span>
        </div>

        <div v-if="results"><h2>Results</h2>
            <div class="alert alert-warning-2" v-if="numKeys == 0">
                No keys detected
            </div>

            <div class="alert alert-warning-2" v-if="numKeys > 0 && onlyMod">
                <strong>Warning:</strong> We detected only a raw RSA modulus in the data. If it is not the intended
                format, please check the key and try again, as the checker did not recognise it.
            </div>

            <div class="alert alert-info-2" v-if="wasSshWithoutPrefix">
                <strong>Suggestion:</strong> Your input looks like a SSH key but due to missing
                format information system did not recognize it. "ssh-rsa " prefix is missing.
                <template v-if="textInput"><br/>
                <a href="#" @click.prevent="addSshRsa">Click here to add the prefix and try again</a>.</template>
            </div>

            <div class="alert alert-info-2" v-if="wasBaseAsn1">
                <strong>Suggestion:</strong> Your input looks like X509 certificate
                or a public key but due to missing format information system did not recognize it.
                <template v-if="textInput"><br/>
                <a href="#" @click.prevent="addAsn1PemFormat">Click here to add ASCII armor and try again</a>
                (assuming there is only one key / certificate).
                </template>
            </div>

            <div class="alert alert-info-2" v-if="wasHexAsn1">
                <template v-if="!textInput">
                    <strong>Suggestion:</strong>
                    Your input starts with "30" which hints it may be hex encoded key representation
                    which system did not recognize. Please use supported key formats.
                </template>
                <template v-else="">
                    Tho format looks like PKCS#1 / ASN.1, which the checker doesn't understand.
                    <template v-if="wasHexOnly">
                        <a href="#" @click.prevent="switchBase64Format">Click here to try our auto-conversion.</a>
                        Be careful, it is not fully tested.
                    </template>
                </template>
            </div>

            <div class="alert alert-info-2" v-if="onlyMod && !wasHexAsn1 && wasHexOnly && !wasPureHexOnly">
                <strong>Suggestion:</strong> The input contains only hex characters with another extra separators.
                System did not recognize the input format. Please use supported key formats.
                <template v-if="textInput && wasHexOnly"><br/>
                If you are sure it is a RAW RSA modulus
                <a href="#" @click.prevent="switchCleanModulus">click here to remove unneeded characters and try again</a>
                (assuming there is only one modulus).
                </template>
            </div>

            <div class="alert alert-success-2" v-if="numKeys > 0 && allSafe && !wasSshWithoutPrefix && !wasHexAsn1 && !onlyMod">
                The {{ pluralize('key', numKeys) }} {{ pluralize('is', numKeys) }} secure
            </div>
            <div class="alert alert-warning-2" v-else-if="!allSafe && allBsiSafe">
                We detected {{ numPositive == 1 ? 'a' : '' }} fingerprinted {{ pluralize('key', numPositive) }} that does not provide the expected security level
                - <a href="#bsi_info">more info</a>.
            </div>
            <div class="alert alert-danger-2" v-else-if="!allSafe">
                We detected insecure {{ pluralize('key', numPositive) }}
            </div>

            <div v-for="(result, r_idx) in results.results" v-if="results.results">

                <h3 v-if="length(results.results) > 1">Key {{ r_idx + 1 }}</h3>

                <div v-for="(test, t_idx) in result.tests" v-if="result.tests && length(results.results) > 0">

                    <h4 v-if="length(result.tests) > 1">Test {{ t_idx + 1 }}</h4>

                    <div>
                        <table class="table">
                            <tbody>
                            <tr v-if="test.type">
                                <th>Type / Interpretation</th>
                                <td>{{ keyType(test.type) }}</td>
                            </tr>
                            <tr v-if="test.kid">
                                <th>Key ID</th>
                                <td>0x{{ test.kid }}</td>
                            </tr>
                            <tr v-if="github && test.fname">
                                <th>Key ID</th>
                                <td>{{ test.fname }}</td>
                            </tr>
                            <tr v-if="test.fprint">
                                <th>Fingerprint</th>
                                <td>{{ test.fprint }}</td>
                            </tr>
                            <tr v-if="test.subject">
                                <th>Subject</th>
                                <td>{{ test.subject }}</td>
                            </tr>
                            <tr v-if="test.issuer">
                                <th>Issuer</th>
                                <td>{{ test.issuer }}</td>
                            </tr>
                            <tr v-if="test.created_at_utc">
                                <th>Created on</th>
                                <td>{{ momentu(test.created_at_utc * 1000.).format('MMM Do YYYY') }}</td>
                            </tr>
                            <tr v-if="test.not_valid_after_utc">
                                <th>Expiring on</th>
                                <td>{{ momentu(test.not_valid_after_utc * 1000.).format('MMM Do YYYY') }}</td>
                            </tr>
                            <tr v-if="test.n">
                                <th>Bit length</th>
                                <td>{{ bitLen(test.n) }}</td>
                            </tr>
                            <tr v-if="hasMarked(test)">
                                <th>Test result</th>
                                <td>
                                    <template v-if="!test.marked">Safe</template>
                                    <template v-else-if="isBsiSafe(test.n)">Fingerprinted key. Does not provide the expected security level.</template>
                                    <template v-else="">Vulnerable</template>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div v-else="">
                    We encountered an error and couldn't complete the test. Please check you used a valid format of
                    your key(s).
                </div>

                <!-- BSI info -->
                <div v-if="!allSafe && allBsiSafe" class="alert alert-info-2">
                    <a name="bsi_info"></a>
                    <strong>Security notice:</strong>
                    All ROCA-fingerprinted public keys have a distinct algebraic structure that significantly reduces the amount
                    of the entropy of the key and does not provide the expected security margin when compared to a
                    randomly generated key of the same length. The keys with lengths of 3072 and 3584 are considered
                    usable for qualified signature creation by German BSI
                    [<a href="https://www.bsi.bund.de/SharedDocs/Zertifikate_CC/CC/Digitale_Signatur-Sichere_Signaturerstellungseinheiten/0833.html"
                        rel="nofollow" target="_blank">link</a>].
                </div>

            </div>

        </div>

    </div>
    </transition>
</template>
<script>
    import _ from 'lodash';
    import axios from 'axios';
    import moment from 'moment';
    import sprintf from 'sprintf-js';
    import Req from 'req';
    import ph4 from 'ph4';
    import testTools from './TesterTools';
    import pluralize from 'pluralize';

    import ToggleButton from 'vue-js-toggle-button';
    import toastr from 'toastr';

    import Vue from 'vue';
    import VueEvents from 'vue-events';

    Vue.use(VueEvents);
    Vue.use(ToggleButton);

    export default {
        props: {
            github: {
                type: Boolean,
                required: false,
                default: false,
            },
            pgp: {
                type: Boolean,
                required: false,
                default: false,
            },
            textInput: {
                type: Boolean,
                required: false,
                default: false,
            },
            lastInput: {
                type: String,
                required: false,
            },

        },

        data: function() {
            return {
                results: null,
                allSafe: true,
                allBsiSafe: true,
                numPositive: 0,
                numPositiveBsi: 0,

                sendingState: 0,
                resultsAvailable: 0,
                displayFlag: 0,
                errorFlag: 0,
            }
        },

        mounted() {
            this.$nextTick(() => {
                this.hookup();
            })
        },

        computed: {
            hasResults(){
                return this.results !== null;
            },
            resultsList(){
                return this.results && this.results.results ?  this.results.results : [];
            },
            numKeys(){
                let num = 0;
                for(const [rkey, result] of Object.entries(this.resultsList)){
                    num += result.tests ? _.size(result.tests) : 0;
                }
                return num;
            },
            onlyMod(){
                let all = 0;
                let onlyMod = 0;
                for(const [rkey, result] of Object.entries(this.resultsList)){
                    for(const [tkey, test] of Object.entries(result.tests)) {
                        if (_.startsWith(test.type, 'mod-')){
                            onlyMod += 1;
                        }
                        all += 1;
                    }

                }
                return all === onlyMod;
            },
            wasSshWithoutPrefix(){
                return !this.errorFlag && this.results && this.lastInput &&
                    _.startsWith(_.trim(this.lastInput), 'AAAA');
            },
            wasHexAsn1(){
                return !this.errorFlag && this.results && this.lastInput &&
                    _.startsWith(_.trim(this.lastInput), '30');
            },
            wasBaseAsn1(){
                return !this.errorFlag && this.results && this.lastInput &&
                    _.startsWith(_.trim(this.lastInput), 'MII');
            },
            wasHexOnly(){
                return !this.errorFlag && this.results && this.lastInput &&
                    /^[a-fA-f0-9:\s\n\t\r]+$/.test(this.lastInput);
            },
            wasPureHexOnly(){
                return !this.errorFlag && this.results && this.lastInput &&
                    /^[a-fA-f0-9]+$/.test(this.lastInput);
            },
            isLoading(){
                return !this.hasResults && !this.errorFlag;
            }
        },

        methods: {
            hookup(){

            },

            pluralize,
            keyType: testTools.keyType,
            bitLen: testTools.bitLen,
            isBsiSafeBl: testTools.isBsiConsideredSafe,

            isBsiSafe(x){
                return testTools.isBsiConsideredSafe(testTools.bitLen(x));
            },

            length(x){
                return x ? _.size(x) : 0;
            },

            momentu(x){
                return moment.utc(x);
            },

            hasMarked(obj){
                return 'marked' in obj;
            },

            addSshRsa(){
                const lines = _.map(_.split(this.lastInput, '\n'), _.trim);
                this.$emit('updateInput',
                    _.join(_.map(lines, x => { return _.size(x) === 0 ? x : 'ssh-rsa ' + x }), '\n'));
            },

            addPemArmor(inp){
                const trimmed = _.trim(inp);
                return '-----BEGIN CERTIFICATE-----\n' +
                    trimmed +
                    '\n-----END CERTIFICATE-----\n\n' +
                    '-----BEGIN PUBLIC KEY-----\n' +
                    trimmed +
                    '\n-----END PUBLIC KEY-----\n\n' +
                    '\n-----BEGIN RSA PUBLIC KEY-----\n' +
                    trimmed +
                    '\n-----END RSA PUBLIC KEY-----\n\n'
            },

            addAsn1PemFormat(){
                this.$emit('updateInput', this.addPemArmor(this.lastInput));
            },

            switchBase64Format(){
                const inp = _.trim(this.lastInput || '').replace(/[:\s\n\t\r]/g, '');
                try {
                    const b64 = new Buffer(inp, 'hex').toString('base64');
                    this.$emit('updateInput', this.addPemArmor(b64));

                } catch(e){
                    toastr.error('Error in the input data, cannot convert', 'Conversion failed', {
                        timeOut: 2000, preventDuplicates: true
                    });
                }
            },

            switchCleanModulus(){
                const inp = _.trim(this.lastInput || '').replace(/[:\s\n\t\r]/g, '');
                this.$emit('updateInput', inp);
            },

            onReset(){
                this.results = null;
                this.allSafe = true;
                this.allBsiSafe = true;
                this.numPositive = 0;
                this.numPositiveBsi = 0;
                this.displayFlag = 1;
                this.errorFlag = 0;
            },

            onWaitingForResults(){
                this.displayFlag = 1;
            },

            onHide(){
                this.displayFlag = 0;
            },

            onError(){
                this.errorFlag = 1;
            },

            onResultsLoaded(data){
                this.results = data;
                this.allSafe = true;
                this.allBsiSafe = true;
                this.numPositive = 0;
                this.numPositiveBsi = 0;
                console.log(data);

                // Results processing.
                if (!this.results){
                    return;
                }

                if (!this.results.results){
                    this.results = [];
                    return;
                }

                for(const [rkey, result] of Object.entries(this.results.results)){
                    if (!result.tests){
                        result.tests = [];
                        continue;
                    }

                    const approved_tests = [];
                    for(const [tkey, test] of Object.entries(result.tests)){
                        const marked = 'marked' in test ? !!test.marked : false;

                        const bl = testTools.bitLen(test.n) || 0;
                        if (_.startsWith(test.type, 'mod-') && bl < 512){
                            continue; // skip this key as it is probably just format error
                        }

                        const bsiSafe = testTools.isBsiConsideredSafe(bl);
                        this.allSafe &= !marked;
                        this.allBsiSafe &= bsiSafe || !marked;
                        this.numPositive += marked ? 1 : 0;
                        this.numPositiveBsi += bsiSafe ? 0 : 1;

                        approved_tests.push(test);
                    }

                    result.tests = approved_tests;
                }

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