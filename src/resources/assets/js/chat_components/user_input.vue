<script>
	export default {
		props: [
			"chatDisabled"
		],
		data() {
			return {
				user_input: "",
				input_type: "text"
			};
		},
		methods: {
			userInput(){
				this.$emit("userInputMessage",{'input_value': this.user_input, 'input_type': this.input_type });
				this.user_input = "";
			},
			changeInputType(){
				this.user_input = "";
				if (this.input_type == 'number') this.input_type = 'text';
				else this.input_type = 'number';
			}
		}
	};
</script>
<template>
	<div class="card-body chat-bottom">
		<div class="row">
			<div class="col-9">
				<input
					class="form-control b-0"
					type="text"
					:disabled="chatDisabled"
					v-if="input_type == 'text'"
					v-model="user_input"
					:placeholder="trans('laravelchat.type_message')"
					@keyup.enter="userInput"
				>
			</div>
			<div class="col-2 text-right">
				<button 
					class="btn btn-info btn-circle btn-md" 
					type="button"
					@click="userInput" 
					:disabled="chatDisabled"
				>
					<i class="fa fa-paper-plane"></i>
				</button>
			</div>
		</div>
	</div>
</template>

<style>
.chat-bottom{
	border-top: 1px solid #eee;
	background-color: white;
	padding: 10px;
}
</style>