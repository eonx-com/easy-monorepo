<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Client;

use EonX\EasyNotification\Enum\MessageStatus;
use EonX\EasyNotification\Message\MessageInterface;
use EonX\EasyNotification\ValueObject\Config;

interface NotificationClientInterface
{
    public function deleteMessage(string $messageId): void;

    /**
     * @param string[] $topics
     * @param array|null $options HTTP Client options
     */
    public function getMessages(array $topics, ?array $options = null): array;

    public function send(MessageInterface $message): void;

    /**
     * @param string[] $messages Messages IDs
     */
    public function updateMessagesStatus(array $messages, MessageStatus $status): void;

    public function withConfig(?Config $config = null): self;
}
