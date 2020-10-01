<script>
	export default {
		props: [
			"user","info"
		],
		data() {
			return { 
			};
		},
		components: {
		},
		methods: {
			errorImage(event){
				this.$emit("errorImage", event.target);
			}
		},
		mounted() {
		},
		created() {
		}
	};
</script>
<template>
	<div>
		<div class="chat-main-header">
			<div class="p-3 border-bottom row" v-if="user">
				<div class="chat-img-title col-1"><img class="chat-img" :src="user.image" @error="errorImage" alt="user"></div>
				<span class="col-6">
					<h3 class="box-title">{{user.name}}</h3>
					<br>
					<span v-if="user.car_brand" class="box-title">{{user.car_brand + " " + user.car_model}}</span>
					<span v-else class="box-title">{{info}}</span>
				</span>
			</div>
			<div v-else>
				CHAT
			</div>
		</div>
	</div>
</template>

<style lang="scss">
.chat-main-header{
	border-bottom: 1px solid grey;
	//background-color: white;
}

.chat-img {
	width: 50px;
	height: 50px;
	border-radius: 50px;
}

</style>