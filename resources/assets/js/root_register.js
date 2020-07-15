// Root Register file should be added into main source file after importing the Vue instance

import Vue from 'vue';
import VueTelInput from 'vue-tel-input';
import BlockUI from 'vue-blockui';
import VueInternationalization from 'vue-i18n';
import Locale from './vue-i18n-locales.generated';
import AirbnbStyleDatepicker from 'vue-airbnb-style-datepicker';
import 'vue-airbnb-style-datepicker/dist/vue-airbnb-style-datepicker.min.css';
import Permissions from './mixins/Permissions.vue';
import { VueMaskDirective } from 'v-mask';
import Multiselect from "vue-multiselect";
import VueTooltip from 'v-tooltip';
import VueSignaturePad from 'vue-signature-pad';
import Popover  from 'vue-js-popover';

Vue.use(Popover, { tooltip: true });

Vue.use(VueSignaturePad);
Vue.use(VueTooltip);
VueTooltip.options.defaultTemplate = '<div class="tooltip-vue" role="tooltip"><div class="tooltip-vue-arrow"></div><div class="tooltip-vue-inner"></div></div>';
VueTooltip.options.defaultArrowSelector = '.tooltip-vue-arrow, .tooltip-vue__arrow';
VueTooltip.options.defaultInnerSelector = '.tooltip-vue-inner, .tooltip-vue__inner';
// import ReadMore from 'vue-read-more';
// Vue.use(ReadMore);
Vue.component('multiselect', Multiselect);
Vue.mixin(Permissions);
Vue.directive('mask', VueMaskDirective);

const datepickerOptions = {};
Vue.use(AirbnbStyleDatepicker, datepickerOptions);

Vue.use(require('vue-moment'));
Vue.use(VueInternationalization);
Vue.use(VueTelInput);
Vue.use(BlockUI);

const lang = document.documentElement.lang.substr(0, 2);
// or however you determine your current app locale

const i18n = new VueInternationalization({
    locale: lang,
    messages: Locale
});