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
	<div class='chat_lib2'>
		<directchat
			:user="{{ $user }}"
			ledger="{{ $ledger_id }}"
			:newconversation="{{ json_encode($new_conversation) }}"
			conversationid="{{ $conversation_id }}"
			:trans="{{ json_encode(trans('laravelchat::laravelchat')) }}"
		/>
	</div>
@stop