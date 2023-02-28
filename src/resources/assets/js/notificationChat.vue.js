window.vue = require('vue');

require('lodash');

import Vue from 'vue';

Vue.prototype.trans = (key) => {
    return _.get(window.lang, key, key);
};

import axios from 'axios';
Vue.prototype.$axios = axios;


import ChatMessageNotification from './pages/ChatMessageNotification.vue';
import ChatPanicNotification from './pages/ChatPanicNotification.vue';

new Vue({
    el: '.notification_chat_lib',

    components: {
        buttonMessageNotification: ChatMessageNotification,
        buttonPanicNotification: ChatPanicNotification
    },
});

