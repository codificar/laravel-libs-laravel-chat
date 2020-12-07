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
	<directchat
		:user="{{ $user }}"
	/>
@stop