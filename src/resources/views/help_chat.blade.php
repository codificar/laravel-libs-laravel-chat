
@extends('layout.master')

@section('content')
	<div id="chat">

		<chat
			environment= "{{ $environment }}"
			request = "{{ json_encode($request) }}"
			request-points = "{{ json_encode($requestPoints) }}"
			user = "{{ json_encode($user) }}"
			maps-api-key="{{$maps_api_key}}"
			laravel_echo_port = "{{ env('LARAVEL_ECHO_PORT', 6001) }}"
			currency-symbol="{{ \Settings::getFormattedCurrency() }}"
			logo = "{{Theme::getLogoUrl()}}"
			help="true"
			message="{{ json_encode($messages) }}"
			admin="{{ json_encode($admin) }}"
			conversation-id="{{ $convId }}"
			>
		</chat>
		
	</div>
@stop


@section('javascripts')
	<script src="/chat/lang.trans/laravelchat"> </script> 
	<script src="{{ elixir('vendor/codificar/chat/chat.vue.js') }}"> </script> 
@endsection
