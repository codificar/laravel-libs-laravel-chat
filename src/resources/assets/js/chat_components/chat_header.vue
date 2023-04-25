<script>
	export default {
		props: [
			"user",
			"info",
			"isConnectedChat"
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
			<div class="chat-connected-div">
				<span 
					:alt="`${isConnectedChat  ? 'Conectado ao chat' : 'Desconectado do chat'}`" 
					:class="`chat-status ${isConnectedChat  ? 'connected' : 'disconnected'}`">
				</span>
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

.chat-connected-div {
	align-items: center;
    display: flex;
	flex: 1;
    justify-content: flex-end;
}

.chat-status {
	height: 10px;
    width: 10px;
    background-color: #00b54200;
    border-radius: 5px;
    cursor: pointer;
    margin-right: 10px;
}

.connected {
	background-color: #00b542;
}

.disconnected {
	background-color: #bf0215;
}
</style>