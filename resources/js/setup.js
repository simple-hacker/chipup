/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap')

// Prevents Facebook's #_=_ url
if (window.location.hash === "#_=_"){
    history.replaceState ? history.replaceState(null, null, window.location.href.split("#")[0]): window.location.hash = ""
}

window.Vue = require('vue')

// vue-snotify
import Snotify from 'vue-snotify'
Vue.use(Snotify)

import VueFormWizard from 'vue-form-wizard'
import 'vue-form-wizard/dist/vue-form-wizard.min.css'
import '@fortawesome/fontawesome-free/css/all.css'
Vue.use(VueFormWizard)

import PokerSetup from '@/components/PokerSetup'

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    components: { PokerSetup }
});
