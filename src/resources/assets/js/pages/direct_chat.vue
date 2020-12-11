<template>
    <div class="chat-app">
        <div class="left-part bg-white fixed-left-part user-chat-box">
            <div class="scrollable position-relative ps-container ps-theme-default" style="height:100%;">
                <div class="p-3 border-bottom">
                    <h5 class="card-title">Filrar conversas</h5>
                    <form>
                        <div class="searchbar">
                            <input v-model="filterName" @keyup="filterResults" class="form-control" type="text" placeholder="Filrar conversas">
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
                                <div>{{ item.full_name }}</div>
                                <span class="font-12 text-nowrap d-block text-muted text-truncate">{{ item.last_message }}</span>
                                <span class="font-12 text-nowrap d-block text-muted text-truncate">{{ item.time }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <div v-for="(item, index) in conversations" :key="index">
                        <div class="message-row" @click="selectConversation(item)">
                            <div class="message-perfil">
                                <img 
                                    class="author-perfil" 
                                    :src="item.picture" alt=""
                                    onerror="this.src='/vendor/codificar/chat/user.png'"
                                >
                            </div>
                            <div class="message-info">
                                <div>{{ item.full_name }}</div>
                                <span class="font-12 text-nowrap d-block text-muted text-truncate">{{ item.last_message }}</span>
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
                <input v-model="textMessage" type="text" placeholder="Digite sua mensagem">
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
                        <h5>Abra uma conversa da lista</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    props: [
        'user',
        'ledger',
        'receiverid',
        'newconversation',
        'conversationid'
    ],
    data() {
        return {
            institution: {},
            conversations: [],
            selectedConversation: null,
            textMessage: '',
            newMessage: '',
            filterName: '',
            filteredConversations: []
        }
    },
    methods: {
        async getConversations() {
            try {
                const response = await axios.get('/api/libs/list_direct_conversation', {
                    params: {
                        id: this.institution.id,
                        token: this.institution.api_key
                    }
                });

                const { conversations } = response.data;
                
                if (this.newconversation) {
                    conversations.unshift(this.newconversation);
                    this.conversations = conversations;
                    this.selectConversation(this.conversations[0]);
                    return;
                }

                this.conversations = conversations;

                for (let i = 0; i < this.conversations.length; i++) {
                    if (this.conversations[i].conversation_id == this.conversationid)
                        this.selectConversation(this.conversations[i])
                }
            } catch (error) {
                this.conversations = [];
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
        async sendMessage() {
            try {
                const response = await axios.post('/api/libs/set_direct_message', {
                    id: this.institution.id,
                    token: this.institution.api_key,
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
                    id: this.institution.id,
                    token: this.institution.api_key,
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
        console.log(this.selectedConversation);
        this.getConversations();
        this.subscribeToChannel(this.institution.default_user_id);
    },
    created() {
        this.institution = this.user.admin_institution.institution;
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
    background-color: #f2f7f8;;
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
</style>