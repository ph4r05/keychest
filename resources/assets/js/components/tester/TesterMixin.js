import _ from 'lodash';
import axios from 'axios';
import moment from 'moment';
import sprintf from 'sprintf-js';
import Req from 'req';
import ph4 from 'ph4';
import toastr from 'toastr';
const uuidv4 = require('uuid/v4');


export default  {
    created: function () {
        // connected
    },

    data() {
        return {
            uuid: null,
            curChannel: null,
            wsStarted: false,
        }
    },

    methods: {

        /**
         * Validity promise.
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

        generateUuid(){
            this.uuid = uuidv4();
            return this.uuid;
        },

        onResult(data){
            // override
        },

        onWsEvent(event){
            try {
                // First websocket event received.
                this.wsStarted = true;
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
         * Ignore the channel
         */
        unlistenWebsocket(){
            try{
                window.Echo.leave(this.curChannel);
            } catch(e){
                console.warn(e);
            }
        },


    }
}
