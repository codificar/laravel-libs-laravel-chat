<script>
	export default {
		props: [
			"user",
			"active"
		],
		data() {
			/**
			 * @author Hugo Couto
			 * 
			 * 
			 */
			return { };
		},
		components: { },
		methods: {
			selectUser(){
				this.$emit("userSelected");
			},
			errorImage(event){
				this.$emit("errorImage", event.target);
			}
		},
		mounted() { },
		created() { }
	};
</script>
<template>
	<div>
		<a @click="selectUser" v-bind:class="{'active' : active}">
			<img :src="user.user.image" @error="errorImage" alt="user-img" class="img-circle" ref="userImg">
			<span>{{ user.user.name}}
				<small class="text-dark">{{user.request.product}}</small>
				<small v-if="user.id%2 == 0 && user.last_bid" class="text-danger">{{ ''+ number_format(user.last_bid, 2, ',', ' ')}}</small>
				<small v-else class="text-warning">{{ trans('laravelchat.no_offer') }}</small>
			</span>
			<div class="last-message">
				<span class="overflow">{{user.last_message.message}}</span>
				<small class="pull-right">{{user.last_message.date}}</small>
			</div>
		</a>
	</div>
</template>

<style lang="scss">
	.last-message {
		display: flex;
		align-content: center;
	}
	.last-message small {
		white-space: nowrap;
	}
	.overflow {
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}
</style>