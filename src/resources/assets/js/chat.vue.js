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

import chat from './pages/request_chat.vue';

new Vue({
    el: '#chat',

    components: {
        chat
    }
})
