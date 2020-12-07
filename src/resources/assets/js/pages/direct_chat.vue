<template>
    <div class="chat-app">
        <div class="left-part bg-white fixed-left-part user-chat-box">
            <div class="scrollable position-relative ps-container ps-theme-default" style="height:100%;">
                <div class="p-3 border-bottom">
                    <h5 class="card-title">Search Contact</h5>
                    <form>
                        <div class="searchbar">
                            <input class="form-control" type="text" placeholder="Search Contact">
                        </div>
                    </form>
                </div>
                <div v-for="(item, index) in conversations" :key="index">
                    <div class="message-row" @click="selectConversation(item)">
                        <div class="message-perfil">
                            <img class="author-perfil" :src="item.picture" alt="">
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
        <div v-if="selectedConversation" class="right-part chat-container">
            <div class="p-20 chat-box-inner-part">
                <div class="card chatting-box mb-0" style="display: block;">
                    <div class="card-body chat-content">
                        <div class="chat-meta-user pb-3 border-bottom chat-active">
                            <div class="current-chat-user-name">
                                <span>
                                    <img :src="selectedConversation.picture" alt="dynamic-image">
                                    <span class="name font-weight-bold ml-2">{{ selectedConversation.full_name }}</span>
                                </span>
                            </div>
                        </div>

                        <div class="chat-box scrollable ps-container ps-theme-default" style="height: calc(100vh - 260px) !important">
                            <div 
                                class="chat-list chat conversation-row" 
                                v-for="(item, index) in selectedConversation.messages" 
                                :key="index"
                            >
                                <div>
                                    <h5 class="text-muted">{{ selectedConversation.full_name }}</h5>
                                    <div class="box mb-2 d-inline-block text-dark rounded p-2 bg-light-info">
                                        {{ item.message }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body border-top chat-send-message-footer chat-active">
                        <div class="row">
                            <div class="col-12">
                                <div class="input-field mt-0 mb-0">
                                    <input id="textarea1" placeholder="Type and hit enter" style="font-family:Arial, FontAwesome" class="message-type-box form-control border-0" type="text">
                                </div>
                            </div>
                        </div>
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
        'user'
    ],
    data() {
        return {
            institution: {},
            conversations: [],
            selectedConversation: {}
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

                const { data } = response;
                this.conversations = data.conversations;

                console.log('teste', data);
            } catch (error) {
                this.conversations = [];
                console.log('getConversations', error);
            }
        },
        subscribeToChannel(id) {
            window.Echo
                .channel(`notifyPanel.${id}`)
                .listen('.PanelNewMessage', async (response) => {
                    this.conversations = response.conversations;
                    console.log('qqqqqq', response);
                });
        },
        selectConversation(data) {
            this.selectedConversation = data;
        }
    },
    mounted() {
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

.chat-box-inner-part {
    height: inherit;
}

.border-bottom {
    border-bottom: 1px solid #eee;
}

.chatting-box {
    height: inherit;
    display: flex;
    flex-direction: column;
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

</style>