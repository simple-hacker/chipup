/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

// vue-router
import VueRouter from 'vue-router';
Vue.use(VueRouter);

// vue-snotify
import Snotify from 'vue-snotify';
Vue.use(Snotify);

// vue-datetime
import Datetime from 'vue-datetime';
import 'vue-datetime/dist/vue-datetime.css';
Vue.use(Datetime);

// vue-number-animation
import VueNumber from 'vue-number-animation';
Vue.use(VueNumber);

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

// Vue.component('login-form', require('./components/LoginForm.vue').default);
// Vue.component('register-form', require('./components/RegisterForm.vue').default);


import LoginForm from './components/LoginForm.vue';
import RegisterForm from './components/RegisterForm.vue';
import App from './App.vue';
import NotFound from './NotFound.vue';

import routes from './routes.js'

import '@fortawesome/fontawesome-free/css/all.css';

const router = new VueRouter({
    mode: 'history',
    routes,
});

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    components: { App, LoginForm, RegisterForm, NotFound },
    router,
});
