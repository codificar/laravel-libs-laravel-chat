@extends('layout.master')

@section('breadcrumbs')
<div class="row page-titles">
	<div class="col-md-6 col-8 align-self-center">
		<h3 class="text-themecolor m-b-0 m-t-0">{{ trans('laravelchat::laravelchat.config_title') }}</h3>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
			<li class="breadcrumb-item active">{{ trans('laravelchat::laravelchat.config_title') }}</li>
		</ol>
	</div>
</div>	
@stop

@section('content')
	<div class='chat_lib2'>
		<chatsettings
            default-admin="{{ $defaultAdmin }}"
            :admins="{{ $admins }}"
        />
	</div>
@stop

@section('javascripts')
	<script src="/chat/lang.trans/laravelchat"> </script> 
	<script src="{{ elixir('vendor/codificar/chat/chat.vue.js') }}"> </script> 
@endsection