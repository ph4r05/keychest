
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
window.URL = require('url-parse');
window.Req = require('./req.js');
window.Psl = require('./lib/psl');
window.Moment = require('moment');
window.toastr = require('toastr');
window.swal = require('sweetalert2');
window.pluralize = require('pluralize');

require('jquery-ui/ui/effects/effect-shake');
require('bootstrap-switch');

/**
 * Admin LTE
 */
require('admin-lte');
require('icheck');

Vue.prototype.trans = (key) => {
    return _.get(window.trans, key, key);
};

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('quicksearch', require('./components/quicksearch.vue'));
Vue.component('feedback_form', require('./components/feedback_form.vue'));

Vue.component('vuetable-my', require('./components/partials/VuetableMy.vue'));
Vue.component('register-form', require('./components/auth/RegisterForm.vue'));
Vue.component('login-form', require('./components/auth/LoginForm.vue'));
Vue.component('email-reset-password-form', require('./components/auth/EmailResetPasswordForm.vue'));
Vue.component('reset-password-form', require('./components/auth/ResetPasswordForm.vue'));
Vue.component('server-tables', require('./components/servers/ServerTables.vue'));
Vue.component('sbox', require('./components/partials/sbox.vue'));
Vue.component('help-modal', require('./components/partials/HelpModal.vue'));
Vue.component('help-trigger', require('./components/partials/HelpTrigger.vue'));
Vue.component('dashboard', require('./components/dashboard.vue'));
Vue.component('subdomains', require('./components/subdomains/Subdomains.vue'));
Vue.component('servers', require('./components/servers.vue'));
Vue.component('servers-import', require('./components/servers-import/server-import.vue'));

console.log('Vue.js init');
const app = new Vue({
    el: '#app'
});
