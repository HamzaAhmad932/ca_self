
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import Vue from "vue";

require('./bootstrap');
require('./root_register');
require('./client');
require('./admin');
require('./auth');
require('./guest');

import store from "./components/store/index";
import Locale from "./vue-i18n-locales.generated";
import VueInternationalization from "vue-i18n";
Vue.use(VueInternationalization);
const lang = document.documentElement.lang.substr(0, 2);
const i18n = new VueInternationalization({
    locale: lang,
    messages: Locale
});

const app = new Vue({
    el: '#app',
 		i18n,
        store,
});

//common bus to identify events
export const bus = new Vue();

