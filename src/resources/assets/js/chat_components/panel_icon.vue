<template>
    <li class="nav-item dropdown">
        <a @click="seeMessage" class="nav-link dropdown-toggle waves-effect waves-dark" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="mdi mdi-message"></i>
            <div v-if="has_notification" class="notify"> 
                <span class="heartbit"></span> 
                <span class="point"></span> 
            </div>
        </a>

        <div class="dropdown-menu mailbox animated bounceInDown">
            <ul class="list-style-none">
                <li>
                    <div class="messages-title">
                        Messages
                    </div>
                </li>
                <li>
                    <div class="message-center notifications position-relative ps-container ps-theme-default" style="height:250px;">
                        <!-- Message -->
                        <div v-for="(item, index) in conversations" :key="index" class="message-row">
                            <div class="message-perfil">
                                <img class="author-perfil" :src="item.picture" alt="">
                            </div>
                            <div class="message-info">
                                <div>{{ item.full_name }}</div>
                                <span class="font-12 text-nowrap d-block text-muted text-truncate">{{ item.last_message }}</span>
                                <span class="font-12 text-nowrap d-block text-muted text-truncate">{{ item.time }}</span>
                            </div>
                        </div>

                        <div class="ps-scrollbar-x-rail" style="left: 0px; bottom: 0px;">
                            <div class="ps-scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div>
                        </div>
                        <div class="ps-scrollbar-y-rail" style="top: 0px; right: 3px;">
                            <div class="ps-scrollbar-y" tabindex="0" style="top: 0px; height: 0px;"></div>
                        </div>
                    </div>
                </li>
                <li>
                    <a class="nav-link border-top text-center text-dark pt-3" href="javascript:void(0);"> <strong>Check all notifications</strong> <i class="fa fa-angle-right"></i> </a>
                </li>
            </ul>
        </div>
    </li>
</template>

<script>
import axios from 'axios';
import Echo from 'laravel-echo';
import {Howl, Howler} from 'howler';

export default {
    props: [
        'user',
        'url'
    ],
    data() {
        return {
            has_notification: false,
            institution: {},
            conversations: []
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
                console.log('#####');
            } catch (error) {
                this.conversations = [];
                console.log('getConversations', error);
            }
        },
        subscribeToChannel(id) {
            window.Echo
                .channel(`notifyPanel.${id}`)
                .listen('.PanelNewMessage', async () => {
                    await this.playSound();
                    this.has_notification = true;
                    this.getConversations();
                    console.log('ssssssss');
                });
        },
        async playSound() {
            var sound = new Howl({
                src: [`${this.url}/vendor/codificar/chat/sound.mp3`],
                autoplay: true
            });

            await sound.play();
        },
        seeMessage() {
            
            this.has_notification = false;
        }
    },
    mounted() {
        window.Echo = new Echo({
			broadcaster: 'socket.io',
			client: require('socket.io-client'),
			host: 'http://127.0.0.1:6001'
		});

        window.io = require('socket.io-client');
        
        this.getConversations();
        this.subscribeToChannel(this.institution.default_user_id);
    },
    created() {
        this.institution = this.user.admin_institution.institution;
    }
}
</script>

<style scoped>
.mailbox {
    width: 300px !important;
}
.messages-title {
    padding-bottom: 16px;
    padding-top: 16px;
    padding-left: 20px;
    font-weight: 500;
    font-size: 16px;
    border-bottom: 1px solid #eee;
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

</style>