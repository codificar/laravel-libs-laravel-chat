<template>
    <div class="chat-app">
        <div class="left-part bg-white fixed-left-part user-chat-box">
            <div class="scrollable position-relative ps-container ps-theme-default" style="height:100%;">
                <div v-if="isAdmin" class="pt-2 pb-2 pl-3 pr-3 border-bottom new-conversation">
                    <p>Nova conversa</p>
                    <a href="#" @click="showModal = true">
                        <i class="mdi mdi-message-text-outline"></i>
                    </a>
                </div>

                <div class="p-3 border-bottom">
                    <h5 class="card-title">{{ trans.filter }}</h5>
                    <form>
                        <div class="searchbar">
                            <input v-model="filterName" @keyup="filterResults" class="form-control" type="text" :placeholder="trans.filter">
                        </div>
                    </form>
                </div>
                <div v-if="filteredConversations.length > 0">
                    <div v-for="(item, index) in filteredConversations" :key="index">
                        <div class="message-row" @click="selectConversation(item)">
                            <div class="message-perfil">
                                <img 
                                    class="author-perfil" 
                                    :src="item.picture"
                                    onerror="this.src='/vendor/codificar/chat/user.png'"
                                >
                            </div>
                            <div class="message-info">
                                <div>{{ item.full_name | nameMaxLength(15) }}</div>
                                <span class="font-12 text-nowrap d-block text-muted text-truncate">{{ item.last_message | nameMaxLength(24) }}</span>
                                <span class="font-12 text-nowrap d-block text-muted text-truncate">{{ item.time }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="infinite-list" class="message-list scrollable ps-active-y" v-else>
                    <div v-for="(item, index) in conversations" :key="index">
                        <div class="message-row" :class="activeSelectStyle(item)" @click="selectConversation(item)">
                            <div class="message-perfil">
                                <img 
                                    class="author-perfil" 
                                    :src="item.picture" alt=""
                                    onerror="this.src='/vendor/codificar/chat/user.png'"
                                >
                            </div>
                            <div class="message-info">
                                <div>{{ item.full_name | nameMaxLength(15) }}</div>
                                <span class="font-12 text-nowrap d-block text-muted text-truncate">{{ item.last_message | nameMaxLength(24) }}</span>
                                <span class="font-12 text-nowrap d-block text-muted text-truncate">{{ item.time }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="selectedConversation" class="right-part chat-container">
            <div class="p-20 chat-box-inner-part">
                <div class="card chatting-box mb-0" style="display: block;">
                    <div class="card-body chat-content">
                        <div class="chat-meta-user pb-3 border-bottom chat-active">
                            <div class="current-chat-user-name">
                                <span>
                                    <img 
                                        :src="selectedConversation.picture" 
                                        onerror="this.src='/vendor/codificar/chat/user.png'"
                                    >
                                    <span class="name font-weight-bold ml-2">{{ selectedConversation.full_name }}</span>
                                </span>
                            </div>
                        </div>

                        <div class="chat-list scrollable ps-active-y" style="height: calc(100vh - 300px)">
                            <div 
                                class="conversation-row" 
                                v-for="(item, index) in selectedConversation.messages" 
                                :key="index"
                            >
                                <div>
                                    <img 
                                        v-if="item.user_id != ledger" 
                                        :src="selectedConversation.picture" 
                                        onerror="this.src='/vendor/codificar/chat/user.png'"
                                    >
                                </div>
                                <div :class="item.user_id == ledger ? 'text-right' : ''">
                                    <h5 v-if="item.user_id != ledger" class="text-muted">{{ selectedConversation.full_name }}</h5>
                                    <p class="box mb-2 d-inline-block text-dark rounded p-2" :class="item.user_id == ledger ? ' bg-light-inverse' : ' bg-light-info'">
                                        {{ item.message }}
                                    </p>
                                </div>
                                <div class="chat-time text-right text-muted">
                                    {{ item.humans_time }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="border-top chat-send-message-footer">
                <input v-model="textMessage" type="text" :placeholder="trans.send_message">
                <a v-if="textMessage" @click="handleSendMessage" href="#">
                    <i class="mdi mdi-send"></i>
                </a>
            </div>
        </div>
        <div v-else>
            <div class="right-part not-selected">
                <div class="chat-not-selected">
                    <div class="text-center">
                        <span class="display-5 text-info"><i class="mdi mdi-comment-outline"></i></span>
                        <h5>{{ trans.open_chat }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <modal 
            v-if="showModal" 
            @close="showModal = false" 
            :canonicalMessages="canonical_messages"
            :locations="locations"
            :user="user"
            @modalSendMessage="onModalSendMessage"
        />
    </div>
</template>

<script>
import modal from '../components/Modal.vue';
import axios from 'axios';
import Echo from 'laravel-echo';

export default {
    props: [
        'user',
        'ledger',
        'receiverid',
        'newconversation',
        'conversationid',
        'trans',
        'environment',
        'echoport'
    ],
    components: {
        modal
    },
    data() {
        return {
            isAdmin: false,
            showModal: false,
            userData: {},
            conversations: [],
            selectedConversation: null,
            textMessage: '',
            newMessage: '',
            filterName: '',
            filteredConversations: [],
            canonical_messages: [],
            locations: [],
            current_page: 1,
            last_page: 1,
            is_loading: false
        }
    },
    filters: {
        nameMaxLength: function (value, limit) {
            if (!value) return '';
            else if (value.length <= limit) return value;
            
            return value.substring(0, limit) + '...';
        }
    },
    methods: {
        async getConversations(page = 1) {
            try {
                const response = await axios.get('/api/libs/filter_conversations', {
                    params: {
                        id: this.userData.id,
                        token: this.userData.api_key,
                        page: isNaN(page) ? 1 : page
                    }
                });

                const { conversations } = response.data;
                const { locations } = response.data;
                const { last_page } = response.data;
                const { current_page } = response.data;

                this.last_page = last_page;
                this.current_page = current_page;
                this.locations = locations;

                if (page == 1) {
                    if (this.newconversation) {
                        conversations.unshift(this.newconversation);
                        this.conversations = conversations;
                        this.selectConversation(this.conversations[0]);
                        return;
                    }
                    console.log('1111', conversations);
                    this.conversations = conversations;
    
    
                    for (let i = 0; i < this.conversations.length; i++) {
                        if (this.conversations[i].conversation_id == this.conversationid)
                            this.selectConversation(this.conversations[i])
                    }
                } else if (page > 1) {
                    for (let index = 0; index < conversations.length; index++) {
                        this.conversations.push(conversations[index]);
                    }
                }
                
                this.is_loading = false;
            } catch (error) {
                this.conversations = [];
                this.is_loading = false;
                console.log('getConversations', error);
            }
        },
        handleSendMessage() {
            if (this.selectedConversation.request_id != 0) {
                this.sendRideMessage();
            } else {
                this.sendMessage();
            }
        },
        activeSelectStyle(item) {
            if (this.selectedConversation) {
                return item.conversation_id == this.selectedConversation.conversation_id ? 'selected-chat' : '';
            }

            return '';
        },
        async sendMessage() {
            try {
                const response = await axios.post('/api/libs/set_direct_message', {
                    id: this.userData.id,
                    token: this.userData.api_key,
                    receiver: this.selectedConversation.id,
                    message: this.textMessage
                });

                const { data } = response;
                this.selectedConversation.messages.push(data.message);

                this.newMessage = data.message;
                this.textMessage = '';
            } catch (error) {
                console.log('sendMessage', error);
            }
        },
        async sendRideMessage() {
            try {
                const response = await axios.post('/api/libs/chat/send', {
                    id: this.userData.id,
                    token: this.userData.api_key,
                    request_id: this.selectedConversation.request_id,
                    message: this.textMessage
                });

                const { data } = response;

                this.selectedConversation.messages.push(data.message);
                
                if (this.selectedConversation.conversation_id == 0)
                    this.selectedConversation.conversation_id = data.conversation_id;
                    
                this.newMessage = data.message;
                this.textMessage = '';
            } catch (error) {
                console.log('sendRideMessage', error);
            }
        },
        filterResults() {
            console.log(this.filterName);

            if (this.filterName.length > 0) {
                this.filteredConversations = this.conversations.filter(query => {
                    return query.full_name.
                        toLowerCase()
                        .includes(this.filterName.toLowerCase());
                });
            } else {
                this.filteredConversations = [];
            }
        },
        subscribeToChannel(id) {
            window.Echo
                .channel(`notifyPanel.${id}`)
                .listen('.PanelNewMessage', async (response) => {
                    this.conversations = response.conversations;

                    if (this.selectedConversation) {
                        for (let i = 0; i < this.conversations.length; i++) {
                            if (this.selectedConversation.conversation_id == this.conversations[i].conversation_id) {
                                this.selectConversation(this.conversations[i])
                            }
                        }
                    }
                });
        },
        selectConversation(data) {
            this.selectedConversation = data;
        },
        /**
         * Request to get canonical messages
         */
        async getCanonicalMessages() {
            try {
                const response = await axios.get('/admin/lib/api/canonical_messages');
                const { data } = response;
                this.canonical_messages = data.messages;
            } catch (error) {
                console.log('getCanonicalMessages', error);
            }
        },
        onModalSendMessage(value) {
            console.log('qqqqqq', value);
            const conversation = this.conversations.filter(query => {
                return query.conversation_id == value.conversation_id;
            });

            if (conversation.length > 0) {
                this.selectConversation(conversation[0]);
                this.selectedConversation.messages.push(value.message);
            } else {
                const newConversation = {
                    conversation_id: value.conversation_id,
                    messages: [value.message],
                    full_name: value.receiver_name,
                    picture: value.receiver_picture,
                    time: value.message.humans_time,
                    last_message: value.message.message
                };

                this.conversations.unshift(newConversation);
                this.selectConversation(this.conversations[0]);
            }

            this.showModal = false;
        }
    },
    watch: {
        selectedConversation: async function() {
            await this.$nextTick();
            var chat = $('.chat-list');
            chat.scrollTop(chat.prop("scrollHeight"));
        },
        newMessage: async function() {
            await this.$nextTick();
            var chat = $('.chat-list');
            chat.scrollTop(chat.prop("scrollHeight"));
        }
    },
    mounted() {
        const listElm = document.getElementById('infinite-list');

        listElm.addEventListener('scroll', async e => {
            if(listElm.scrollTop + listElm.clientHeight >= (listElm.scrollHeight - 200) && this.is_loading == false) {
                this.is_loading = true;
                await this.getConversations(this.current_page + 1);
            }
        });

        this.getConversations();
        this.subscribeToChannel(this.userData.default_user_id);
    },
    created() {
        if (this.environment == 'corp') {
            this.userData = this.user.admin_institution.institution;
        } else if (this.environment == 'admin') {
            window.Echo = new Echo({
                broadcaster: 'socket.io',
                client: require('socket.io-client'),
                host: `${window.location.hostname}:${this.echoport}`
            });

            window.io = require('socket.io-client');

            this.isAdmin = true;
            this.userData = this.user;
            this.userData.api_key = 'token';
            this.userData.default_user_id = 'id';
        }

        this.getCanonicalMessages();
    }
}
</script>

<style>
.container-fluid {
    margin: 0px;
    padding: 0px;
}

* {
    outline: 0;
}

*, ::after, ::before {
    box-sizing: border-box;
}

.left-part {
    position: absolute;
    height: 100%;
    width: 260px;
    border-right: 1px solid #e9ecef;
}

.chat-app {
    background: #fff;
}

.message-row {
    display: flex;
    justify-content: row;
    padding: 10px 20px;
    border-bottom: 1px solid #eee;
    max-height: 75px;
    cursor: pointer;
}

.message-row:hover {
    background-color: #f2f7f8;
}

.message-perfil {
    display: flex;
    justify-content: center;
    align-items: center;
}

.message-info {
    margin-left: 12px;
}

.message-info div:nth-child(1) {
    font-size: 1rem;
    font-weight: 400;
    color: #212529;
}

.font-12 {
    font-size: 12px;
}

.author-perfil {
    width: 40px;
    height: 40px;
    border-radius: 50px;
}

.right-part {
    width: calc(100% - 260px);
    height: calc(100vh - 125px);
    margin-left: 260px;
}

.border-bottom {
    border-bottom: 1px solid #eee;
}

.card-body {
    flex: 1 1 auto;
    min-height: 1px;
}

.border-top {
    border-top: 1px solid #eee!important;
}

.current-chat-user-name img {
    width: 45px;
    height: 45px;
    border-radius: 50px;
}

.scrollable {
    position: relative;
}

.chat-list {
    overflow-y: auto;
}

.chat-list::-webkit-scrollbar {
  width: 20px;
}

.chat-list::-webkit-scrollbar-track {
  background-color: transparent;
}

.chat-list::-webkit-scrollbar-thumb {
  background-color: #d6dee1;
  border-radius: 20px;
  border: 6px solid transparent;
  background-clip: content-box;
}

.conversation-row {
    display: flex;
    flex-direction: row;
    margin-top: 17px;
}

.conversation-row div:nth-child(1) img {
    width: 45px;
    height: 45px;
    border-radius: 40px;
    margin-right: 15px;
}

.conversation-row div:nth-child(2) {
    width: calc(100% - 70px);
}

.conversation-row div:nth-child(2) p {
    margin-right: 15px;
}

.chat-time {
    font-size: 12px;
    width: 70px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.chat-send-message-footer{
    height: 65px;
    display: flex;
    flex-direction: row;
    align-items: center;
    padding: 0 15px;
}

.chat-send-message-footer input {
    border: none;
    width: calc(100% - 70px);
}

.chat-container {
    display: flex;
    flex-direction: column;
    align-content: space-between;
}

.not-selected {
    display: flex;
    align-items: center;
    justify-content: center;
}

.chat-box-inner-part {
    height: 100%;
}

.chat-send-message-footer a {
    font-size: 26px;
}

.new-conversation {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
}

.new-conversation p {
    margin-bottom: 0;
}

.new-conversation a {
    font-size: 32px;
}

.selected-chat {
    background-color: #f2f7f8;
}

.message-list {
    height: 75vh;
    overflow: auto;
}

.message-list::-webkit-scrollbar {
  width: 20px;
}

.message-list::-webkit-scrollbar-track {
  background-color: transparent;
}

.message-list::-webkit-scrollbar-thumb {
  background-color: #d6dee1;
  border-radius: 20px;
  border: 6px solid transparent;
  background-clip: content-box;
}
</style>