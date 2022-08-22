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
			}
		},
		created() {
			console.log('Message List', this);
		},
		watch: {
			conversation: async function() {
				await this.$nextTick();
				var chat = $('#message-list');
				chat.scrollTop(chat.prop("scrollHeight"));
			}
		}
	};
</script>
<template>
	<div class="chat-rbox">
		<ul 
			class="chat-list p-3"
			id="message-list"
		>
			<!--chat Row -->
			<li 
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
						<h5 v-else>Nome não encontrado</h5>

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
    align-items: end;
}
</style>