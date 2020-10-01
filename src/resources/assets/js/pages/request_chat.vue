<script>
import ChatComponent from './chat_component.vue';
import HelpChatComponent from './help_chat.vue';
export default {
	props: [
		"environment",
		"laravel_echo_port",
		"Request",
		"RequestPoints",
		"User",
		"Institution",
		"mapsApiKey",
		"logo",		
		"currencySymbol",
		'help',
		'message',
		'admin',
		'ConversationId'
	],
	data() {
		/**
		 * @author Hugo Couto
		 * 
		 * 
		 */
		return {
			request: JSON.parse(this.Request),
			request_points: JSON.parse(this.RequestPoints),
			user: JSON.parse(this.User),
			institution: this.Institution ? JSON.parse(this.Institution) : ''
		};
	},
	components: {
		ChatComponent,
		HelpChatComponent
	},
	methods: {
		acceptOffer(){
			this.$swal({
				title: "Accept Offer?",
				type: 'warning',
				showCancelButton: true,
				confirmButtonText: this.trans('yes'),
				cancelButtonText: this.trans('no')
				}).then((result) => {});
		}
	},
	mounted() {
	},
	created() {
	}
};
</script>
<template>
	<div class="full-panel">
		<div class="row full-panel absolute" >
			<div class="col col-md-4 full-panel">
				<div class="card card-outline-info left-panel">
					<div class="card-header">
						<div class="row align-items-center">
							<div class="col-sm-12">
								<h3 class="card-title text-white m-b-0"> {{ trans('requests.chat_request') }} </h3>
							</div>
						</div>
					</div>
					<div class="card-block">
						<div class="col-md-12 col-sm-12" style="overflow-x: hidden;">
							<table class="table" >
								<tbody>
									<tr v-if="environment == 'admin'">
										<td v-if="institution"> {{ trans('requests.institution') }} </td>
										<td v-else> {{ trans('requests.user') }} </td>
										<td v-if="institution"> {{ institution.name }} </td>
										<td v-else> {{ user.name }} </td>
									</tr>
									<tr v-for="(point, index) in request_points" :key="point.id" >
										<td v-if="index == 0"> {{ trans('requests.origin') }} </td>
										<td v-else-if="index != (request_points.length -1)"> {{ trans('requests.next_address') }} </td>
										<td v-else> {{ trans('requests.destination') }} </td>
										<td class="text-overflow" :data-text="point.address"> {{ point.address }} </td>
									</tr>
									<tr>
										<td>{{ trans('requests.estimate_distance') }} </td>
										<td> {{ number_format(request.estimate_distance, 2, ',','') }} Km </td>
									</tr>
									<tr>
										<td>{{ trans('requests.estimate_time') }} </td>
										<td> {{ number_format(request.estimate_time, 2, ',','') }} min </td>
									</tr>
									<tr>
										<td>{{ trans('requests.estimate_price') }} </td>
										<td> {{ currency_format(request.estimate_price, currencySymbol) }} </td>
									</tr><!--
									<tr>
										<td>{{ trans('requests.accept_negotiate_value') }} </td>
										<td v-if="select_negotiate_value"> {{ trans('requests.yes') }} </td>
										<td v-else> {{ trans('requests.no') }} </td>
									</tr>
									<tr>
										<td>{{ trans('payment.payment_method') }} </td>
										<td> {{ trans('payment.' + select_payment_method) }} </td>
									</tr>-->
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="col col-md-8 full-panel" v-if="!help">
				<ChatComponent 
					:laravel_echo_port="laravel_echo_port" 
					:user="user" 
					:environment="environment" 
					:channel="request.id" 
					:logo="logo"
					:admin="admin"
					>
				</ChatComponent>
			</div>
			<div class="col col-md-8 full-panel" v-else>
				<HelpChatComponent 
					:laravel_echo_port="laravel_echo_port" 
					:user="User" 
					:environment="environment" 
					:channel="request.id" 
					:logo="logo"
					:message="message"
					:admin="admin"
					:conversation-id="ConversationId"
					>
				</HelpChatComponent>
			</div>
		</div>
	</div>
</template>

<style lang="scss">
.container-fluid {
	height: 88vh;
}


.full-panel {
	height: 100%;
	width: 100%;
	padding: 0px;
	border-width: 0px;
	margin: 0px;
	left: 0px;
	top: 0px;
}
.absolute {
	position: absolute;
}
.left-panel {
	z-index: 0;
	height: 100%;
	width: 100%;
	overflow-y: auto;
}





.text-overflow{
	overflow: hidden;
	white-space: nowrap;
	text-overflow: ellipsis;
}
.text-overflow:focus, .text-overflow:hover {
  color:transparent;
}
.text-overflow:focus:after,.text-overflow:hover:after{
	content:attr(data-text);
	overflow: visible;
	text-overflow: inherit;
	background: #fff;
	position: absolute;
	left:auto;
	top:auto;
	width: auto;
	max-width: 20rem;
	border: 1px solid #eaebec;
	padding: 0 .5rem;
	box-shadow: 0 2px 4px 0 rgba(0,0,0,.28);
	white-space: normal;
	word-wrap: break-word;
	display:block;
	color:black;
	margin-top:-1.25rem;
	z-index: 10;
  }

</style>