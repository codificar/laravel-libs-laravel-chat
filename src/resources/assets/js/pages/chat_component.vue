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
		"admin"
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
			adminUser: {}
		};
	},
	components: {
		UserList,
		ChatHeader,
		MessageList,
		UserInput
	},
	methods: {
		userSelected(conversation){
			this.conversation_active = conversation;
			this.getMessages(conversation.id);
		},
		sendMessage(data){
			var vm = this, type = data.input_type == 'number'?'bid':'text';

			axios.post(`/api/libs/${vm.environment}/chat/send`, {
				token: vm.User.token,
				user_id: vm.User.user_id,
				provider_id: vm.User.provider_id,
				request_id: vm.channel,
				receiver_id: vm.conversation_active.user.id,
				type: type,
				message: type == 'text'?data.input_value:'',
				bid: type == 'text'?'':data.input_value
			})
		},
		subscribeToChannel(conversationId) {
			if(conversationId == 0) return;
			var vm = this;
			window.Echo.channel('conversation.' + conversationId )
			.listen('.readMessage', e => {
				if(e.message.conversation_id == vm.conversation_active.id) {
					vm.getMessages(e.message.conversation_id);
				}
			})
			.listen('.newMessage', e => {
				vm.getConversations();
				if(e.message.conversation_id == vm.conversation_active.id) {
					if(vm.messages.every(message => message.id != e.message.id))
						vm.messages.push(e.message);
				}
			});
		},
		subscribeToChannelRequest(requestId) {
			var vm = this;
			window.Echo.channel('request.' + requestId).listen('.newConversation', e => {
				vm.getConversations();
			});
		},
		getConversations() {
			var vm = this;
			axios.get(`/api/libs/${vm.environment}/chat/conversation`, {
				params: {
					token: vm.User.token,
					user_id: vm.User.user_id,
					provider_id: vm.User.provider_id,
					request_id: vm.channel,
				}
			}).then(response => {
				vm.conversationArray = response.data.conversations;
				vm.conversationArray.forEach(e => {
					vm.subscribeToChannel(e.id);
				});
				if(vm.conversation_active.id == 0 && vm.conversationArray.length > 0) {
					if(vm.conversationArray[0].id == 0) {
						vm.subscribeToChannelRequest(vm.channel);
					}
					vm.userSelected(vm.conversationArray[0]);
				}
			});
		},
		getMessages(conversationId) {
			var vm = this;
			axios.get(`/api/libs/${vm.environment}/chat/messages`, {
				params: {
					token: vm.User.token,
					user_id: vm.User.user_id,
					provider_id: vm.User.provider_id,
					conversation_id: conversationId,
					limit: 10
				}
			}).then(response => {
				if(response.data.messages)
					vm.messages = response.data.messages;
			});
		},
		setAsSeen(messageId) {
			var vm = this;
			axios.post(`/api/libs/${vm.environment}/chat/seen`, {
				token: vm.User.token,
				user_id: vm.User.user_id,
				provider_id: vm.User.provider_id,
				message_id: messageId
			});
		},
		hasUnseen(){
			return this.messages.some(e => { return !e.is_seen && e.user_id != this.User.id });
		},
		errorImage(obj) {
			obj.src = this.logo;
		}
	},
	watch: {
		messages: function () {
			if(this.hasUnseen()){
				var lastMessage = this.messages[this.messages.length-1];
				this.setAsSeen(lastMessage.id);
			}
		}
	},
	mounted() {
		window.Echo = new Echo({
			broadcaster: 'socket.io',
			client: require('socket.io-client'),
			host: window.location.hostname + ":" + this.laravel_echo_port
		});

		window.io = require('socket.io-client');
		
		if(this.environment != 'provider') {
			this.subscribeToChannelRequest(this.channel);
		}
		//Obtém as conversas
		this.getConversations();
	},
	created() {
		if (this.admin) {
			this.adminUser = JSON.parse(this.admin);
		}
	}
};
</script>
<template>
	<div class="full-panel">
		<!-- .chat-left-panel 
		<UserList @errorImage="errorImage" @userSelected="userSelected" :conversations="conversationArray" v-if="environment != 'provider'" ref="userList">
		</UserList>-->
		<!-- .chat-left-panel -->
		<!-- .chat-right-panel -->
		<div class="chat-right-aside">
			<ChatHeader @errorImage="errorImage" :user="conversation_active.user" :info="conversation_active.request.product"/>
			<MessageList
				@errorImage="errorImage"
				:conversation="messages"
				:user-one="User"
				:user-two="conversation_active.user"
				:admin="adminUser"
				ref="messageList"
				:logo="logo"
			/>
			<UserInput @userInputMessage="sendMessage" :chat-disabled="conversation_active.user == undefined"/>
		</div>
		<!-- .chat-right-panel -->
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