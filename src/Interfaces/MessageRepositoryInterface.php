<?php

namespace Codificar\Chat\Interfaces;

interface MessageRepositoryInterface
{
	/**
	 * Get all messages help unread
	 * @return array
	 */
	public function getAllMessagesHelpUnread(): array;
	/**
	 * Get all messages panic today
	 * @return array
	 */
	public function getAllMessagesPanicToday(): array;
	/**
	 * Get request help message by id 
	 * @param int $requestHelpId
	 * @return array
	 */
	public function getMessageHelpById(int $requestHelpId): array;

    /**
     * set al messages as read by conversation and/or user
     * @param int $conversationId
     * @param int $messageId
     * @param int $userId - default null
     * 
     * @return void
     */
	public function setMessagesAsSeen(int $conversationId, int $messageId, int $userId = null): void;
}
