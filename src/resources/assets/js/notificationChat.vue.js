window.vue = require('vue');

require('lodash');

import Vue from 'vue';
import axios from 'axios';

Vue.prototype.$axios = axios;
Vue.prototype.trans = (key) => {
    return _.get(window.lang, key, key);
};

import ChatMessageNotification from './pages/ChatMessageNotification.vue';

new Vue({
    el: '.notification_chat_lib',
    components: {
        buttonMessageNotification: ChatMessageNotification
    },
});

