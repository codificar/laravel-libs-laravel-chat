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
		data() {
			/**
			 * @author Hugo Cout
			 * 
			 */
			return { };
		},
		components: {
		},
		methods: {
			errorImage(event){
				this.$emit("errorImage", event.target);
			}
		},
		watch: {
			conversation: async function() {
				//Rola scroll para o fim
				await this.$nextTick();
				var chat = $('#message-list');
				chat.scrollTop(chat.prop("scrollHeight"));
			}
		},
		mounted() { },
		created() { }
	};
</script>
<template>
	<div class="chat-rbox">
		<ul class="chat-list p-5" style="overflow-y: auto; width: auto; height: 100%;" id="message-list">
			<!--chat Row -->
			<li v-for="message in conversation" :key="message.id" v-bind:class="{'reverse' : message.user_id == userOne.id}">
				<div v-if="message.user_id == userOne.id" style="display: flex; flex-direction: row;">
					<div class="chat-content" >
						<h5>{{ !message.admin_id ? userOne.name : admin.name}}</h5>
						<div class="box bg-light-info">
							{{message.message}}
							<span class="fa fa-check" v-if="message.is_seen" style="color:green;"></span>
						</div>
					</div>
					<div style="display: flex; flex-direction: column; align-items: center;">
						<div class="chat-img"><img :src="!message.admin_id ? userOne.image : admin.image" @error="errorImage" alt="user"></div>
						<div class="chat-time" style="text-align: center;">{{message.humans_time}}</div>
					</div>
				</div>
				<div v-else style="display: flex; flex-direction: row;">
					<div style="display: flex; flex-direction: column; align-items: center;">
						<div class="chat-img"><img :src="!message.admin_id ? userTwo.image : admin.image" @error="errorImage" alt="user"></div>
						<div class="chat-time" style="text-align: center;">{{message.humans_time}}</div>
					</div>
					<div class="chat-content">
						<h5>{{ !message.admin_id ? userTwo.name : admin.name }}</h5>
						<div class="box bg-light-inverse">{{message.message}}</div>
					</div>
				</div>
			</li>
			<!--chat Row -->
		</ul>
	</div>
</template>

<style lang="scss">

.chat-rbox{
	//height: 85%;
	height: calc(100% - 125px);
	background-color: white;
}

</style>