<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Interfaces;

interface NotificationClientInterface
{
    public function deleteMessage(string $messageId): void;

    /**
     * @param string[] $topics
     * @param mixed[]|null $options HTTP Client options
     *
     * @return mixed[]
     */
    public function getMessages(array $topics, ?array $options = null): array;

    public function send(MessageInterface $message): void;

    /**
     * @param string[] $messages Messages IDs
     */
    public function updateMessagesStatus(array $messages, string $status): void;

    public function withConfig(?ConfigInterface $config = null): self;
}
