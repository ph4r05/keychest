import _ from 'lodash';
import axios from 'axios';
import moment from 'moment';
import sprintf from 'sprintf-js';
import Req from 'req';
import ph4 from 'ph4';
import toastr from 'toastr';
import uuidv4 from 'uuid/v4';


export default  {
    created() {
        // connected
    },

    data() {
        return {
            uuid: null,
            curChannel: null,
            wsStarted: false,
            testResults: null,
            resultWaitExpirationTimer: null,
        }
    },

    methods: {

        /**
         * Validity promise wrapper.
         * @param res
         * @param invalidError
         * @returns {Promise}
         */
        validCheck(res, invalidError){
            return new Promise((resolve, reject) => {
                if (res) {
                    resolve();
                    return;
                }

                toastr.error(invalidError, 'Check failed', {
                    timeOut: 2000, preventDuplicates: true
                });
                reject();
            });
        },

        /**
         * Generates UUID for the key test job.
         * @returns {*}
         */
        generateUuid(){
            this.uuid = uuidv4();
            return this.uuid;
        },

        onResult(data){
            // override
        },

        onResultWaitTimeout(){
            // override
        },

        /**
         * Event handler for socket.io - testresult event.
         * @param event
         */
        onWsEvent(event){
            try {
                // First websocket event received.
                this.wsStarted = true;

                // Results arrived - cancel timeout timer
                this.cancelResultsTimer();

                // In this version we expect only single message - results.
                this.unlistenWebsocket();

                this.onResult(event.data);

            } catch(e){
                console.warn(e);
            }
        },

        /**
         * Enables listening to the channel
         */
        listenWebsocket(uuid){
            this.curChannel = 'keytest.' + (uuid || this.uuid);
            console.log('listen: ' + this.curChannel);
            try {
                window.Echo
                    .channel(this.curChannel)
                    .listen('.keytest.event', this.onWsEvent);

            } catch(e){
                console.warn(e);
            }
        },

        /**
         * Aborts results gathering - both timer and socket
         */
        abortResults(){
            this.cancelResultsTimer();
            this.unlistenWebsocket();
        },

        /**
         * Ignore the channel
         */
        unlistenWebsocket(){
            try{
                window.Echo.leave(this.curChannel);
            } catch(e){
                console.warn(e);
            }

            // Just a fuse not to trigger.
            this.cancelResultsTimer();
        },

        /**
         * Starts timer waiting for results to come.
         * If timer is fired results are deemed as lost.
         * @param timerVal
         */
        scheduleResultsTimeout(timerVal){
            this.resultWaitExpirationTimer = setTimeout(this.onResultWaitTimeout,
                timerVal || (30000));
        },

        /**
         * Stops result wait timeout timer.
         */
        cancelResultsTimer(){
            try {
                if (this.resultWaitExpirationTimer) {
                    clearTimeout(this.resultWaitExpirationTimer);
                    this.resultWaitExpirationTimer = null;
                }
            } catch(e){
                console.warn(e);
            }
        },


    }
}
