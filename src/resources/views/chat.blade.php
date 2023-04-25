<?php $layout = ''; ?>
@switch($environment)
    @case('admin')
		<?php $layout = '.master'; ?>
        @break

	@case('corp')
		<?php $layout = '.corp.master'; ?>
        @break

	@case('user')
		<?php $layout = '.user.master'; ?>
	@break

	@case('provider')
		<?php $layout = '.provider.master'; ?>
	@break

    @default
		@break
@endswitch
@extends('layout'.$layout)

@section('content')
	<div class="chat_lib" style="width: 99%;">
		<chat
			environment= "{{ $environment }}"
			request = "{{ json_encode($request) }}"
			request-points = "{{ json_encode($requestPoints) }}"
			user = "{{ json_encode($user) }}"
			institution = "{{ json_encode($institution) }}"
			admin="{{ isset($userAdmin) ? json_encode($userAdmin) : '' }}"
			laravel_echo_port = "{{ env('LARAVEL_ECHO_PORT', 6001) }}"
			currency-symbol=""
			logo = "{{Theme::getLogoUrl()}}"
			>
		</chat>
	</div>
@endsection

@section('javascripts')
	<script src="/chat/lang.trans/laravelchat"> </script> 
	<script src="{{ asset('vendor/codificar/chat/chat.vue.js') }}"> </script> 
@endsection
