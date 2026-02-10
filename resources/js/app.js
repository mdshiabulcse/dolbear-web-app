require('./bootstrap');

// window.Vue = require('vue');
import Vue from 'vue/dist/vue';
Vue.config.productionTip = true;
Vue.config.devtools = true;
Vue.config.debug = true;
// coolLightBox
import CoolLightBox from 'vue-cool-lightbox';
import 'vue-cool-lightbox/dist/vue-cool-lightbox.min.css';

Vue.use(CoolLightBox);
//vue-router
import VueRouter from 'vue-router';

import VueObserveVisibility from 'vue-observe-visibility';
Vue.use(VueObserveVisibility)

Vue.use(VueRouter);
//vue-axios
import { routes } from "./routes/frontend";

import axios from 'axios';
import VueAxios from 'vue-axios';

Vue.use(VueAxios, axios)

import vSelect from 'vue-select';

// Patch v-select to fix onSearch prop warning
const originalMounted = vSelect.mounted;
vSelect.mounted = function() {
    // Remove the onSearch prop if it's boolean false
    if (this.$options.propsData && this.$options.propsData.onSearch === false) {
        delete this.$options.propsData.onSearch;
    }
    if (originalMounted) {
        return originalMounted.call(this);
    }
};

Vue.component('v-select', vSelect);

//Vuex
import VuePlyr from 'vue-plyr';
import Vuex from 'vuex';

Vue.use(Vuex);

Vue.use(VuePlyr, {
    plyr: {}
});

import helper from './helper';
import storeData from './store/index';
import module from './store/module';

// Import Analytics helper for tracking
import Analytics from './helpers/analytics';
window.Analytics = Analytics;

const store = new Vuex.Store({
    modules: {
        module,
        storeData
    }
});
export default store;

import objectToFormData from "./objectToFormData";

window.objectToFormData = objectToFormData;

Vue.component('frontend_master', require('./components/frontend/frontend_master').default);
Vue.component('loading_button', () => import('./components/frontend/partials/loading_button'));

import VueProgressBar from 'vue-progressbar';

const options = {
    color: 'var(--primary-color)',
    failedColor: '#bb2d3b',
    thickness: '2px',
    transition: {
        speed: '0.2s',
        opacity: '0.6s',
        termination: 300
    },
    autoRevert: true,
    location: 'top',
    inverse: false
}

Vue.use(VueProgressBar, options);

import Vue2Filters from 'vue2-filters';

Vue.use(Vue2Filters);

// Register filterBy filter for vue-select compatibility
Vue.filter('filterBy', function (array, filterKey, filterValue) {
    if (!array) return [];
    if (!filterKey || !filterValue) return array;

    return array.filter(item => {
        return item[filterKey] === filterValue;
    });
});

import { initializeApp } from "firebase/app";

function getValueFromId(id)
{
    let value = '';
    let input_box = document.getElementById(id);

    if (input_box)
    {
        value = input_box.value;
    }
    return value;
}

const firebaseConfig = {
    apiKey: getValueFromId('api_key'),
    authDomain: getValueFromId('auth_domain'),
    projectId: getValueFromId('project_id'),
    storageBucket: getValueFromId('storage_bucket'),
    messagingSenderId: getValueFromId('messaging_sender_id'),
    appId: getValueFromId('app_id'),
    measurementId: getValueFromId('measurement_id')
};

// Initialize Firebase
const firebase_app = initializeApp(firebaseConfig);

const router = new VueRouter({
    mode: 'history',
    base: app_path,
    history: true,
    routes,
    scrollBehavior(to, from, savedPosition) {
        return {x: 0, y: 0}
    }
});

new Vue({
    el: '#app',
    router,
    mixins: [helper],
    store,
});

router.afterEach((to, from) => {
    // GA4 Page View Tracking for SPA Navigation
    if (window.dataLayer) {
        window.dataLayer.push({
            event: 'page_view',
            page_path: to.path,
            page_title: to.meta.title || document.title,
            page_location: window.location.href
        });
        console.log('[Analytics] SPA Page View tracked:', to.path);
    }

    // Legacy GA (Universal Analytics) - deprecated but kept for compatibility
    if (window.ga && ga.create) {
        ga('set', 'page', to.path);
        ga('send', 'pageview');
    }
});