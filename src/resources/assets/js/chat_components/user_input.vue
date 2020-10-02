<script>
	export default {
		props: [
			"chatDisabled"
		],
		data() {
			/**
			 * @author Hugo Couto
			 * 
			 * 
			 */
			return {
				user_input: "",
				input_type: "text"
			};
		},
		components: {
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
		},
		mounted() {
		},
		created() {
		}
	};
</script>
<template>
	<div class="card-body chat-bottom">
		<div class="row">
			<div class="col-9">
				<input
					type="text"
					v-bind:disabled="chatDisabled"
					v-if="input_type == 'text'"
					v-model="user_input"
					:placeholder="trans('laravelchat.type_message')"
					class="form-control b-0"
					v-on:keyup.enter="userInput"
				>
			</div>
			<div class="col-2 text-right">
				<button @click="userInput" type="button" class="btn btn-info btn-circle btn-lg" v-bind:disabled="chatDisabled"><i class="fa fa-paper-plane"></i> </button>
			</div>
		</div>
	</div>
</template>

<style lang="scss">
.chat-bottom{
	border-top: 1px solid grey;
	background-color: white;
	padding-top: 3px;
}
</style>