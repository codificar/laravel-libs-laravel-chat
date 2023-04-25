<script>
	export default {
		props: {
			userOne: {
				type: Object
			},
			userTwo: {
				type: Object
			},
			conversation: {
				type: Array
			},
			admin: {
				type: Object
			},
			isNewMessage: {
				type: Boolean,
				required: false,
				default: false
			},
			isSend: {
				type: Boolean
			},
			isConversation: {
				type: Boolean,
				default: false
			},
			isLoadingChat: {
				type: Boolean,
				default: true
			},
			readMessages: {
				type: Function
			},
			scrollToMessage: {
				type: Boolean
			},
			hideAlertNewMessage: {
				type: Function
			},
		},
		async created() {
			await this.showNewMessage();
			const self = this;
			$(document).ready(function() { 
				var chat = $('#message-list');
				chat.on('scroll', function(event) {
					const positionScroll = $(this)[0].clientHeight + $(this).scrollTop(); 
					const endScroll = $(this)[0].scrollHeight - 100;
					if(positionScroll >= endScroll) {
						self.readMessages();
					}
				});
			});
		},
		watch: {
			scrollToMessage: async function() {
				const vm = this;
				if(this.scrollToMessage) {
					await this.$nextTick();
					var chat = $('#message-list');
					chat.scrollTop(chat.prop("scrollHeight"));
					vm.hideAlertNewMessage();
				}
			},
			conversation: async function() {
				await this.$nextTick();
			},
		},
		methods: {
			showNewMessage: async function() {
				var chat = $('#message-list');
				chat.scrollTop(chat.prop("scrollHeight"));
				this.hideAlertNewMessage();
				this.readMessages();
			},
		},
	};
</script>
<template>
	<div class="chat-rbox">
		<div v-if="isLoadingChat" class="chat-list-img">
			<p class="text-loading">{{ trans('laravelchat.loading_chat') }}</p>
		</div>
		<ul
			v-else 
			class="chat-list p-3"
			id="message-list"
		>
			<!-- init Message Row -->
			<li 
				v-if="!isConversation"
				class="reverse">
				<div class="chat-list-row center-message text-center">
					<div class="chat-content center-message text-center" >
						<div class="box bg-light-info">
							{{ trans('laravelchat.init_chat') }}
						</div>
					</div>
				</div>
			</li>
			
			<!-- Chat Row -->
			<li
				v-else
				v-for="message in conversation" 
				:key="message.id"
				v-bind:class="{'reverse' : message.user_id == userOne.id}"
			>
				<div 
					v-if="!message.admin_id && message.user_id == userOne.id" 
					class="chat-list-row left"
				>
					<div class="chat-content left-message" >
						<h5>{{ !message.admin_id ? userOne.name : admin.name}}</h5>
						<div class="box bg-light-info">
							{{message.message}}
							<span class="fa fa-check" v-if="message.is_seen" style="color:green;"></span>
						</div>
					</div>
					<div class="chat-list-img">
						<div>
							<img class="chat-img" :src="!message.admin_id ? userOne.image : admin.image"  alt="user">
						</div>
						<div class="chat-time" style="text-align: center;">
							{{message.humans_time}}
						</div>
					</div>
				</div>
				
				<div 
					v-else-if="message.admin_id && message.admin_id == userOne.id" 
					class="chat-list-row left"
				>
					<div class="chat-content left-message">
						<h5 v-if="!message.admin_id">{{ userOne.name }}</h5>
						<h5 v-else-if="message.admin_id && admin.name">{{ admin.name }}</h5>
						<h5 v-else-if="message.admin_id && userOne.admin_institution && userOne.admin_institution.institution" >
							{{ userOne.admin_institution.institution.name }}
						</h5>
						<h5 v-else>{{ trans('laravelchat.name_not_found') }}</h5>

						<div class="box bg-light-info">
							{{message.message}}
							<span class="fa fa-check" v-if="message.is_seen" style="color:green;"></span>
						</div>
					</div>
					<div class="chat-list-img">
						<div>
							<img class="chat-img" :src="!message.admin_id ? userOne.image : admin.image"  alt="user">
						</div>
						<div class="chat-time" style="text-align: center;">
							{{message.humans_time}}
						</div>
					</div>
				</div>
				
				<div 
					v-else-if="!message.admin_id && !message.is_provider && message.user_name" 
					class="chat-list-row left"
				>
					<div class="chat-content left-message">
						<h5 v-if="message.user_name">{{ message.user_name }}</h5>
						<h5 v-else>Nome não encontrado</h5>

						<div class="box bg-light-info">
							{{message.message}}
							<span class="fa fa-check" v-if="message.is_seen" style="color:green;"></span>
						</div>
					</div>
					<div class="chat-list-img">
						<div>
							<img class="chat-img" :src="message.user_picture"  alt="user">
						</div>
						<div class="chat-time" style="text-align: center;">
							{{message.humans_time}}
						</div>
					</div>
				</div>

				<div 
					v-else 
					class="chat-list-row right"
				>
					<div class="chat-list-img">
						<div>
							<img class="chat-img" :src="!message.admin_id ? userTwo.image : admin.image"  alt="user">
						</div>
						<div class="chat-time" style="text-align: center;">
							{{message.humans_time}}
						</div>
					</div>
					<div class="chat-content">
						<h5>{{ !message.admin_id ? userTwo.name : admin.name }}</h5>
						<div class="box bg-light-inverse">
							{{message.message}}
						</div>
					</div>
				</div>
				
			</li>
			<!--chat Row -->
		</ul>

 		<transition name="fade">
			<div class="container-new-message"
				v-if="isNewMessage"
				@click="showNewMessage()">
				<div class="button-new-message">
					<p class="text-new-message">{{ trans('laravelchat.new_message') }}</p>
					<i class="fa fa-angle-down icon-new-message"></i>
				</div>
			</div>
		</transition>
	</div>
</template>
<style>
.chat-list {
	overflow-y: auto; 
	width: auto; 
	height: 100%;
}

.chat-list-row {
	display: flex; 
	flex-direction: row;
}

.chat-list-img {
	display: flex; 
	flex-direction: column;
	align-items: center;
}

.chat-img {
	width: 45px;
	height: 45px;
}

.chat-rbox{
	height: calc(100% - 125px);
	background-color: white;
}

.reverse {
	margin-top: 10px !important;
}

.left-message {
	display: flex !important;
    flex-direction: column;
    flex: 1;
    justify-content: center;
    align-items: flex-end;
}
.container-new-message {
	display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    bottom: 45px;
	transform-origin: bottom; /* changed to bottom */
  	transition: transform .3s ease-in-out;
	overflow: hidden;
}
.button-new-message {
	display: flex;
	flex-direction: column;
    justify-content: center;
    align-items: center;
    background-color: #27850f;
    padding: 10px 10px 7px 10px;
    border-radius: 15px;
    height: 30px;
    cursor: pointer;
}
.text-new-message, .icon-new-message {
	color: #fff;
	font-weight: bold;
	font-size: 12px;
	margin: 0px;
}

.icon-new-message {
	position: relative;
    top: -3px;
}

.slide-enter, .slide-leave-to{
  transition: scaleY(0);
}

.center-message {
	justify-content: center;
}

.text-loading {
	margin-top: 5px;
	font-size:18px;
}
</style>