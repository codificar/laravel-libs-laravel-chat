<script>
import UserListElement from './user_list_element.vue'
	export default {
		props: [
			"conversations",
		],
		data() {
			/**
			 * @author Hugo Couto
			 * 
			 * 
			 */
			return {
				active: { id: 0 },
				search_contact: ""
			};
		},
		components: {
			UserListElement
		},
		methods: {
			selectUser(conversation){
				this.active = conversation;
				this.$emit("userSelected", conversation);
			},
			errorImage(obj){
				this.$emit("errorImage", obj);
			}
		},
		mounted() { },
		created() { }
	};
</script>
<template>
	<div class="chat-left-aside">
		<div class="open-panel"><i class="ti-angle-right"></i></div>
		<div class="chat-left-inner" style="height: 100%;">
			<div class="form-material">
				<input v-model="search_contact" class="form-control p-3" type="text" placeholder="Search Contact">
			</div>
			<div class="slimScrollDiv" style="position: relative; overflow: hidden; width: auto; height: 100%;">
				<ul class="chatonline style-none " style="overflow-y: auto; width: auto; height: 93%;" v-if="conversations.length > 0">
					<li v-for="(conversation) in conversations" v-bind:key="conversation.id">
						<UserListElement @errorImage="errorImage" @userSelected="selectUser(conversation)" :user="conversation" :active="active.id == conversation.id"/>
					</li>
					<li class="p-3"></li>
				</ul>
				<span c-else class="chatonline">
					{{trans('laravelchat.no_conversation')}}
				</span>
			<div class="slimScrollBar" style="background: rgb(220, 220, 220); width: 5px; position: absolute; top: 0px; opacity: 0.4; display: none; border-radius: 7px; z-index: 99; right: 1px; height: 100%;"></div><div class="slimScrollRail" style="width: 5px; height: 100%; position: absolute; top: 0px; display: none; border-radius: 7px; background: rgb(51, 51, 51); opacity: 0.2; z-index: 90; right: 1px;"></div></div>
		</div>
	</div>
</template>

<style lang="scss">

.chat-left-aside{
	height: 100%;
}

span.chatonline {
	padding: 5px;
}
</style>