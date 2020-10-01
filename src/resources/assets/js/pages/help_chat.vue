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
		/**
		 * @author Hugo Couto
		 * 
		 */
		return {
			conversation_active: {
				id: 0,
				request: {}
			},
			search_contact: "",
			conversationArray: [],
            messages: [],
			userAdmin: {},
			user: {}
		};
	},
	components: {
		UserList,
		ChatHeader,
		MessageList,
		UserInput
	},
	methods: {
        sendMessage(data) {
            axios.post(`/api/v3/set_help_message`, {
				token: this.userAdmin.token,
				id: this.userAdmin.user_id,
                request_id: this.channel,
				message: data.input_value,
				conversation_id: this.ConversationId
			})
        },
        getMessages() {
            axios.get(`/api/v3/get_help_message`, {
                params: {
                    token: this.userAdmin.token,
                    id: this.userAdmin.user_id,
					request_id: this.channel,
					conversation_id: this.ConversationId
                }
            }).then(res => {
                const { data } = res;
                this.messages = data.messages;
                console.log(res.data);
            })
		},
		subscribeToChannel(conversationId) {
			if(conversationId == 0) return;
			window.Echo.channel('conversation.' + conversationId )
			.listen('.newMessage', e => {
				this.getMessages();
			});
		},
		errorImage(obj) {
			obj.src = this.logo;
		}
	},
	mounted() {
		window.Echo = new Echo({
			broadcaster: 'socket.io',
			client: require('socket.io-client'),
			host: window.location.hostname + ":" + this.laravel_echo_port
		});

		window.io = require('socket.io-client');
		this.subscribeToChannel(this.ConversationId);
	},
	created() {
        this.messages = JSON.parse(this.message);
		this.userAdmin = JSON.parse(this.admin);
		this.user = JSON.parse(this.User)
    }
};
</script>
<template>
	<div class="full-panel">
		<div class="chat-right-aside">
			<ChatHeader @errorImage="errorImage" :user="user" />
			<MessageList
				@errorImage="errorImage"
				:conversation="messages"
				:user-one="userAdmin"
				:user-two="user"
				ref="messageList"
				:logo="logo"
			/>
            <UserInput 
                @userInputMessage="sendMessage" 
                :chat-disabled="User == undefined"
            />
		</div>
	</div>
</template>

<style lang="scss">

.full-panel {
	height: 100%;
	width: 100%;
	padding: 0px;
	border-width: 0px;
	margin: 0px;
	left: 0px;
	top: 0px;
}

.chat-left-aside{
	height: 100%;
}

.chat-right-aside{
	height: 95%;
}
.chat-bottom{
	border-top: 1px solid grey;
	background-color: white;
	padding-top: 3px;
}

.chat-img-title{
	display: inline-block;
	width: 65px;
	img {
		width: 60px;
	}
}
.box-title{
	display: inline-block;
}
.chat-main-header{
	border-bottom: 1px solid grey;
	//background-color: white;
}

</style>