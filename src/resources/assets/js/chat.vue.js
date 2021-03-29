window.vue = require('vue');

require('lodash');

import Vue from 'vue';

Vue.prototype.trans = (key) => {
    return _.get(window.lang, key, key);
};

Vue.prototype.currency_format = (number, currency, intPrecision = 2, chrDecimal = ',', chrThousand = '.') => {
    
    if(currency == 'GS')
        intPrecision = 0;
    
    return currency + ' ' + Vue.prototype.number_format(number, intPrecision, chrDecimal, chrThousand);
}

Vue.prototype.number_format = (number, decimals, dec_point, thousands_point) => {

    if (number == null || !isFinite(number)) {
        return number; //Retornando assim para ajudar a identificar o local do erro
        throw new TypeError("number '" + number + "' is not valid");
    }

    if (decimals != 0 && !decimals) {
        var len = number.toString().split('.').length;
        decimals = len > 1 ? len : 0;
    }

    if (!dec_point) {
        dec_point = '.';
    }

    if (!thousands_point) {
        thousands_point = ',';
    }

    number = parseFloat(number).toFixed(decimals);

    number = number.replace(".", dec_point);

    var splitNum = number.split(dec_point);
    splitNum[0] = splitNum[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousands_point);
    number = splitNum.join(dec_point);

    return number;
}

import axios from "axios";
Vue.prototype.$axios = axios;

import pagination from 'laravel-vue-pagination';
Vue.component('pagination', pagination);

import chat from './pages/request_chat.vue';
import reporthelp from './pages/reporthelp.vue';
import directchat from './pages/direct_chat.vue';
import chatpanelicon from './chat_components/panel_icon.vue';
import canonical from './pages/canonical_messages.vue';

new Vue({
    el: '.chat_lib',

    components: {
        chat,
        reporthelp,
        chatpanelicon
    }
});

new Vue({
    el: '.chat_lib2',

    components: {
        directchat,
        canonical
    }
});
