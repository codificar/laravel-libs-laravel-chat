<script>
import UserList from '../chat_components/user_list.vue';
import ChatHeader from '../chat_components/chat_header.vue';
import MessageList from '../chat_components/message_list.vue';
import UserInput from '../chat_components/user_input.vue';
import Echo from 'laravel-echo';
import axios from 'axios';

export default {
	props: [
        'User',
        'laravel_echo_port',
        'request',
        'environment',
        'channel',
        'logo',
        'admin',
        'conversationId'
	],
	data() {
		return {
			conversation_active: {
				id: this.conversationId || 0,
				request: {}
			},
			search_contact: "",
			conversationArray: [],
			messages: [],
			adminUser: {},
			isConnectedChat: true,
			isConversation: true,
			isLoadingChat: true,
			scrollToMessage: false,
			intervalConversation: null,
		};
	},
	components: {
		UserList,
		ChatHeader,
		MessageList,
		UserInput
	},
	methods: {
		async userSelected(conversation){
			this.conversation_active = conversation;
			if(conversation && conversation.id != 0) {
				await this.getMessages(conversation.id);
			}
			this.scrollToMessage = true;
		},
		hideAlertNewMessage() {
			this.scrollToMessage = false;
			this.isNewMessage = false;
		},
		sendMessage(data) {
			var vm = this, type = data.input_type == 'number'?'bid':'text';
			vm.scrollToMessage = true;
			vm.isConversation = true;

			let conversationId = 0;
			if(vm.conversation_active.id != 0) {
			 	conversationId = vm.conversation_active.id;
			}

			axios.post(`/api/libs/${vm.environment}/chat/send`, {
				token: vm.User.token,
				user_id: vm.User.user_id,
				provider_id: vm.User.provider_id,
				request_id: vm.channel,
				receiver_id: vm.conversation_active.user.id,
				type: type,
				conversation_id: conversationId,
				message: type == 'text'?data.input_value:'',
				bid: type == 'text'?'':data.input_value
			}).then(async response => {
				if(response.data.success && response.data.conversation_id) {
					vm.subscribeToChannel(response.data.conversation_id);
				}
			});
		},
		async subscribeToChannel(conversationId) {
			var vm = this;

			if(conversationId == 0) return;

			await window.Echo.leave('conversation.' + conversationId );
			await window.Echo.channel('conversation.' + conversationId )
				.listen('.readMessage', e => {
					vm.isConnectedChat = true;
					const isActiveConversation = e.message.conversation_id == vm.conversation_active.id
					if(isActiveConversation) {
						vm.getMessages(e.message.conversation_id);
					}
				})
				.listen('.newMessage', e => {
					vm.isConnectedChat = true;
					vm.isLoadingChat = false;
                    vm.isNewMessage = false;
                    vm.getConversations();
					
					const isActiveConversation = e.message.conversation_id == vm.conversation_active.id
					if(isActiveConversation) {
                        const existMessage = vm.messages.some(m => m.id == e.message.id);
						if(!existMessage) {
							vm.messages.push(e.message);
                            const isAdmin = e.message.admin_id;
                            const isUser = !isAdmin && !e.message.is_provider == 1;
                            const isProvider = !isUser && e.message.is_provider == 1;
                            
							if(e.message.is_seen == 0 && (isProvider || isUser) ) {
                                //Alert new message
                                vm.isNewMessage = true;
                            }

							if(isAdmin) {
								vm.scrollToMessage = true;
							}
						}
					}
				}).error((error) =>{
					console.error('Error Tryng connect/listen socket:', error);
				});
		},
		subscribeToChannelRequest(requestId) {
			var vm = this;

            if (requestId == 0) return;

			window.Echo.leave('request.' + requestId);
			window.Echo.channel('request.' + requestId)
				.listen('.newConversation', e => {
					vm.getConversations();
				}).error((error) =>{
                console.error('Error Tryng connect/listen socket:', error);
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

				if(vm.conversation_active.id == 0) {
					if(vm.conversationArray.length > 0) {
						vm.isConversation = true;
						vm.isLoadingChat = false;
						vm.conversation_active = vm.conversationArray[0];
						if(vm.conversation_active.id == 0) {
							vm.subscribeToChannelRequest(vm.channel);
						}
						vm.userSelected(vm.conversationArray[0]);
					}
				}

			});
		},
		getMessages(conversationId) {
			var vm = this;
            let token = vm.User.token;
            let userId = null;
            let providerId = null;
            
            if(vm.request.user_id) {
                userId = vm.request.user_id;
            } else if(vm.User.user_id) {
                userId = vm.User.user_id;
            } else if(vm.User.id) {
                userId = vm.User.id;
            }

            if(vm.request.confirmed_provider) {
                providerId = vm.request.confirmed_provider.id;
            } else if(vm.User.provider_id) {
                providerId = vm.User.provider_id;
            }

			axios.get(`/api/libs/${vm.environment}/chat/messages`, {
				params: {
					token: token,
					user_id: userId,
					provider_id: providerId,
					conversation_id: conversationId,
					request_id: vm.channel,
					limit: 10,
				}
			}).then(response => {
				if(response.data.messages && response.data.messages.length > 0) {
					vm.isConversation = true;
					vm.isLoadingChat = false;
					vm.messages = response.data.messages;
				}
				if(response.data.conversation_id) {
					vm.subscribeToChannel(response.data.conversation_id);
				}
			});
		},
		readMessages() {
			var vm = this;
			vm.isNewMessage = false;
			vm.messages.forEach(async (message, key) =>{
				if(message.is_seen == 0 && message.is_provider == 1) {
					vm.messages[key].is_seen = 1;
					await vm.setAsSeen(message.id);
				}
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
			return this.messages.some(e => { return !e.is_seen && e.is_provider });
		},
		errorImage(obj) {
			obj.src = this.logo;
		}
	},
	watch: {
		messages: function () {
			if(this.hasUnseen()){
				var lastMessage = this.messages[this.messages.length-1];
				//this.setAsSeen(lastMessage.id);
			}
		},
		conversation_active: function() {
			const vm = this;
			const isConversationId = vm.conversation_active 
				&& parseInt(vm.conversation_active.id) !== 0;

			if(isConversationId) {
				vm.isConversation = true;
				vm.isLoadingChat = false;
				clearInterval(vm.intervalConversation);
			} else {
				vm.isConversation = false;
			}
		}
	},
	mounted() {
		const vm = this;
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

		// inicia o interval para pegar o conversation id
		vm.intervalConversation = setInterval(() => {
			const isConversationId = vm.conversation_active 
				&& parseInt(vm.conversation_active.id) !== 0;
			if(!isConversationId) {
				this.getConversations();
			}
		}, 5000);
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
		<div class="chat-right-aside">
			<ChatHeader 
				@errorImage="errorImage" 
				:user="conversation_active.user" 
				:info="conversation_active.request.product"
				:isConnectedChat="isConnectedChat"
			/>
			<MessageList
				@errorImage="errorImage"
				:conversation="messages"
				:user-one="User"
				:user-two="conversation_active.user"
				:admin="adminUser"
				:readMessages="readMessages"
				ref="messageList"
				:logo="logo"
				:isNewMessage="isNewMessage"
				:isConversation="isConversation"
				:isLoadingChat="isLoadingChat"
				:scrollToMessage="scrollToMessage"
				:hideAlertNewMessage="hideAlertNewMessage"
			/>
			<UserInput 
				@userInputMessage="sendMessage" 
				:chat-disabled="conversation_active.user == undefined"
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
	img {
		width: 60px;
	}
}
.box-title{
	display: inline-block;
}
.chat-main-header{
	border-bottom: 1px solid grey;
}

</style>