<script>
	export default {
		props: [
			"user",
			"info"
		]
	};
</script>
<template>
	<div>
		<div class="chat-main-header" v-if="user">
			<div class="chat-img-div">
				<img class="chat-img" :src="user.image">
			</div>
			<div class="chat-name-div">
				<h3 class="box-title">{{user.name}}</h3>
				<br>
				<span v-if="user.car_brand" class="box-title">{{user.car_brand + " " + user.car_model}}</span>
			</div>
		</div>
		<div v-else>
			CHAT
		</div>
	</div>
</template>
<style>
.chat-main-header{
	border: 1px solid #eee;
	display: flex;
	flex-direction: row;
	padding: 10px;
	background: #fff;
}

.chat-img-div {
	width: 50px;
	margin-right: 20px;
	display: flex;
	justify-content: center;
	align-items: center;
}

.chat-name-div {
	display: flex;
	justify-content: center;
	align-items: center;
}

.chat-img {
	width: 50px;
	height: 50px;
	border-radius: 50px;
}
</style>