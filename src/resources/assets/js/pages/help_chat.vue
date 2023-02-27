<script>
import UserList from '../chat_components/user_list.vue';
import ChatHeader from '../chat_components/chat_header.vue';
import MessageList from '../chat_components/message_list.vue';
import UserInput from '../chat_components/user_input.vue';
import Echo from 'laravel-echo';
import axios from 'axios';

export default {
	props: [
		"User",
		"laravel_echo_port",
		"environment",
		"channel",
        "logo",
        'message',
		'admin',
		'ConversationId'
	],
	data() {
		return {
			conversation_active: {
				id: 0,
				request: {}
			},
			search_contact: "",
			conversationArray: [],
            messages: [],
			userAdmin: {},
			user: {},
			isConversation: true,
			isNewMessage: false,
			isLoadingChat: false,
			isConnectedChat: true,
			scrollToMessage: true,
		};
	},
	components: {
		UserList,
		ChatHeader,
		MessageList,
		UserInput
	},
	methods: {
        async sendMessage(data) {
            await axios.post(`/api/libs/set_help_message`, {
				token: this.userAdmin.token,
				id: this.userAdmin.user_id,
                request_id: this.channel,
				message: data.input_value,
				conversation_id: this.ConversationId
			});
        },
        async getMessages() {
			const vm = this;
            await axios.get(`/api/libs/get_help_message`, {
                params: {
                    token: this.userAdmin.token,
                    id: this.userAdmin.user_id,
					request_id: this.channel,
					conversation_id: this.ConversationId
                }
            }).then(res => {
                const { data } = res;
                vm.messages = data.messages;
				vm.isConversation = true;
				vm.isConnectedChat = true;
				vm.isLoadingChat = false;
            }).catch(error => {
				vm.isNewMessage = false;
				vm.isConnectedChat = false;
				vm.isLoadingChat = false;
				console.log(error);
			});
		},
		readMessages() {
			this.isNewMessage = false;
		},
		subscribeToChannel(conversationId) {
			const vm = this;
			if(conversationId == 0) return;
			window.Echo.channel('conversation.' + conversationId )
			.listen('.newMessage', e => {
				vm.getMessages();
			});
		},
		errorImage(obj) {
			obj.src = this.logo;
		},
		hideAlertNewMessage() {
			this.scrollToMessage = false;
			this.isNewMessage = false;
		},
	},
	async mounted() {
		// Define o client e broadcaster
		const client = require('socket.io-client');
		const broadcaster = 'socket.io';

		const host = this.echoHost || window.location.hostname;
		const port = this.echoPort || 6001
		// Abre a conexão
		if(!window.Echo) {
			window.Echo = new Echo({
				broadcaster: broadcaster,
				client: client,
				host: `${host}:${port}`
			});
		}

		if(window.io) {
			window.io = client;
		}
		this.subscribeToChannel(this.ConversationId);
	},
	created() {
		this.messages = JSON.parse(this.message);
		this.userAdmin = JSON.parse(this.admin);
		this.user = JSON.parse(this.User);
		
    }
};
</script>
<template>
	<div class="full-panel">
		<div class="chat-right-aside">
			<ChatHeader
				@errorImage="errorImage" 
				:user="user" 
				:is-connected-chat="isConnectedChat" />
			<MessageList
				@errorImage="errorImage"
				:conversation="messages"
				:user-one="userAdmin"
				:user-two="user"
				:read-messages="readMessages"
				ref="messageList"
				:logo="logo"
				:is-new-message="isNewMessage"
				:is-conversation="isConversation"
				:is-loading-chat="isLoadingChat"
				:scroll-to-message="scrollToMessage"
				:hide-alert-new-message="hideAlertNewMessage"
			/>
            <UserInput
                @userInputMessage="sendMessage" 
                :chat-disabled="User == undefined"
            />
		</div>
	</div>
</template>

<style>

.full-panel {
	height: 100%;
	width: 100%;
	padding: 0px;
	border-width: 0px;
	margin: 0px;
	left: 0px;
	top: 0px;
}

.chat-right-aside{
	height: 99%;
}

.chat-bottom{
	border-top: 1px solid grey;
	background-color: white;
	padding-top: 3px;
}

.chat-img-title{
	display: inline-block;
	width: 65px;
}
.box-title{
	display: inline-block;
}
.chat-main-header{
	border-bottom: 1px solid grey;
}

</style>