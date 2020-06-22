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

// vue-router
import VueRouter from 'vue-router'
Vue.use(VueRouter)

// vue i18n
import VueI18n from 'vue-i18n'
Vue.use(VueI18n)

// vue-snotify
import Snotify, { SnotifyPosition } from 'vue-snotify'

const options = {
  toast: {
    position: SnotifyPosition.centerTop
  }
}

Vue.use(Snotify, options)

// vue-datetime
import Datetime from 'vue-datetime'
import 'vue-datetime/dist/vue-datetime.css'
Vue.use(Datetime)

// vue-number-animation
import VueNumber from 'vue-number-animation'
Vue.use(VueNumber)

// vue-apexcharts
import VueApexCharts from 'vue-apexcharts'
Vue.use(VueApexCharts)
Vue.component('apexchart', VueApexCharts)

// vue-js-modal
import VModal from 'vue-js-modal'
Vue.use(VModal, {
    dialog: true,
    dynamic: true,
    dynamicDefaults: {
        adaptive: true,
    },
})

// vue-js-toggle-button
import ToggleButton from 'vue-js-toggle-button'
Vue.use(ToggleButton)

//v-show-slide
import VShowSlide from 'v-show-slide'
Vue.use(VShowSlide)

// Only import the isEqual function from loadash.
// Used for comparing two objects.
import { isEqual, debounce } from 'lodash'
Vue.use(isEqual, debounce)

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

// Vue.component('login-form', require('./components/LoginForm.vue').default)
// Vue.component('register-form', require('./components/RegisterForm.vue').default)


import App from '@/App'
import NotFound from '@/NotFound'

import routes from '@/routes'
import store from '@store/store'

import '@fortawesome/fontawesome-free/css/all.min.css'

const router = new VueRouter({
    mode: 'history',
    routes,
})

import { locales as numberFormats, currencies } from '@/currencies'

const i18n = new VueI18n({
    locale: 'en-GB',
    numberFormats,
})

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    components: { App, NotFound },
    router,
    store,
    i18n
})
