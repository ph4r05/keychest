
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import Vue from 'vue';
import Req from 'req';

window.Vue = Vue;
window.Req = Req;

require('./common.js');
require('jquery-ui/ui/effects/effect-shake');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('quicksearch', require('./components/quicksearch.vue'));
Vue.component('quicksearch-main', require('./components/quicksearch-main.vue'));
Vue.component('feedback_form', require('./components/feedback_form.vue'));
Vue.component('help-modal', require('./components/partials/HelpModal.vue'));
Vue.component('help-trigger', require('./components/partials/HelpTrigger.vue'));

console.log('Vue.js init');
const app = new Vue({
    el: '#app',
    mounted() {
        this.$nextTick(() => {
            window.Req.bodyVueLoaded(true);
        })
    },
});

window.VueMain = app;

