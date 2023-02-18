<?php

namespace Codificar\Chat\Interfaces;

interface MessageRepositoryInterface
{
	/**
	 * Get all messages help unread
	 * @return array
	 */
	public function getAllMessagesHelpUnread();
	/**
	 * Get all messages panic today
	 * @return array
	 */
	public function getAllMessagesPanicToday();
}
