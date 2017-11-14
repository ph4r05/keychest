
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

// __webpack_public_path__ = '/';  // quick hack for Vue2 lazy loaded components. Not needed now.
require('./bootstrap');

import _ from 'lodash';
import Vue from 'vue';
import VueRouter from 'vue-router';
import VueResource from 'vue-resource';
import Req from 'req';

window.Vue = Vue;
window.Req = Req;

require('moment-timezone');
require('./common.js');

require('jquery-ui/ui/effects/effect-shake');
require('bootstrap-switch');

/**
 * Admin LTE
 */
require('admin-lte');

Vue.prototype.trans = (key) => {
    return _.get(window.trans, key, key);
};

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('sbox', require('./components/partials/sbox.vue'));
Vue.component('vuetable-my', require('./components/partials/VuetableMy.vue'));

Vue.component('help-modal', require('./components/partials/HelpModal.vue'));
Vue.component('help-trigger', require('./components/partials/HelpTrigger.vue'));

Vue.component('feedback_form', require('./components/feedback_form.vue'));
Vue.component('register-form', require('./components/auth/RegisterForm.vue'));
Vue.component('login-form', require('./components/auth/LoginForm.vue'));
Vue.component('email-reset-password-form', require('./components/auth/EmailResetPasswordForm.vue'));
Vue.component('reset-password-form', require('./components/auth/ResetPasswordForm.vue'));

Vue.component('quicksearch', resolve => {
    require.ensure([], require => resolve(require('./components/quicksearch.vue')), 'quicksearch');
});

Vue.component('dashboard', resolve => {
    require.ensure([], require => resolve(require('./components/dashboard.vue')), 'dashboard');
});

Vue.component('servers', resolve => {
    require.ensure([], require => resolve(require('./components/servers.vue')), 'servers');
});
Vue.component('ip_servers', resolve => {
    require.ensure([], require => resolve(require('./components/ip_servers.vue')), 'ip_servers');
});
Vue.component('subdomains', resolve => {
    require.ensure([], require => resolve(require('./components/subdomains/Subdomains.vue')), 'subdomains');
});
Vue.component('server-tables', resolve => {
    require.ensure([], require => resolve(require('./components/servers/ServerTables.vue')), 'server-tables');
});
Vue.component('servers-import', resolve => {
    require.ensure([], require => resolve(require('./components/servers-import/server-import.vue')), 'servers-import');
});
Vue.component('account', resolve => {
    require.ensure([], require => resolve(require('./components/license/Account.vue')), 'account');
});
Vue.component('tester', resolve => {
    require.ensure([], require => resolve(require('./components/tester/Tester.vue')), 'tester');
});


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

// Fill in missing time zones
window.Req.timezoneCheck();
