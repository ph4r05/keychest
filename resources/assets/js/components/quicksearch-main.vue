<template>

    <div class="bloc bloc-fill-screen tc-onyx bgc-white l-bloc" id="intro" style="height: 400px;"
         v-bind:class="{'kc-search': searchEnabled, 'kc-loading': !searchEnabled && !resultsLoaded, 'kc-results': resultsLoaded}" >
        <div class="container">
            <div class="row">

                <div class="col-sm-12">
                    <img src="/images/logo2-rgb_keychest.png" alt="Certificate monitoring KeyChest logo" class="img-responsive center-block" width="300">
                    <h3 class="text-center mg-lg hero-bloc-text-sub  tc-rich-electric-blue">
                        Check, watch, and plan for 100% HTTPS uptime
                    </h3>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-8 col-sm-offset-2">
                    <quicksearch ref="quicksearch"
                                 v-on:onRecompNeeded="recomp"
                                 v-on:onError="onError"
                                 v-on:onSearchStart="onSearchStart"
                                 v-on:onResultsLoaded="onResultsLoaded"
                                 v-on:onReset="onReset"
                                 :landing="true"
                    ></quicksearch>
                </div>
            </div>

            <!-- Buttons section -->
            <div class="row search-buttons" v-if="searchEnabled">
                <!-- Logged in vs. new visitor -->
                <div v-bind:class="{
                            'col-sm-6': Laravel.authGuest,
                            'col-sm-2': !Laravel.authGuest,
                            'col-sm-offset-5': !Laravel.authGuest,
                            'text-center': !Laravel.authGuest}">
                    <a class="btn btn-lg btn-rich-electric-blue" v-bind:class="{'pull-right' : Laravel.authGuest}"
                       id="btn-check-expire" v-on:click.stop="submitForm()">
                        Spot check</a>
                </div>

                <div class="col-sm-6" v-if="Laravel.authGuest">
                    <a class="btn btn-lg pull-left btn-rich-electric-blue" v-bind:href="Laravel.urlRegister">
                        Dashboard</a>
                </div>
            </div>
            <!-- End of buttons section -->

        </div>

        <div class="container fill-bloc-bottom-edge" v-if="searchEnabled">
            <div class="row row-no-gutters">
                <div class="col-sm-12">
                    <a id="scroll-hero" class="blocs-hero-btn-dwn" href="https://keychest.net/#"><span class="fa fa-chevron-down"></span></a>
                </div>
            </div>
        </div>

    </div>


</template>

<script>
    export default {
        data: function() {
            return {
                resultsLoaded: false,
                searchEnabled: true,

                Req: window.Req,
                Laravel: window.Laravel
            };
        },

        mounted() {
            this.$nextTick(function () {
                this.hookup();
            })
        },

        methods: {

            transition_hook(el){
                this.recomp();
            },

            recomp(){
                try {
                    setTimeout(setFillScreenBlocHeight, 0);
                }catch(e){
                    setTimeout(this.recomp, 1000);
                }
            },

            hookup(){
                // Hide loading placeholders
                Req.bodyProgress(false);
                $('#intro-placeholder').hide();
            },

            onError(msg){
                this.resultsLoaded = false;
            },
            onSearchStart(){
                this.searchEnabled = false;
                this.resultsLoaded = false;
            },
            onResultsLoaded(){
                this.resultsLoaded = true;
                this.recomp();
            },
            onReset(){
                this.resultsLoaded = false;
            },
            submitForm(){
                this.$refs.quicksearch.submitForm();
            }
        }
    }
</script>

<style>
.scan-results-host {
    padding-left: 5px;
    padding-right: 5px;
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 1.0s
}
.fade-enter, .fade-leave-to /* .fade-leave-active in <2.1.8 */ {
    opacity: 0
}
</style>

