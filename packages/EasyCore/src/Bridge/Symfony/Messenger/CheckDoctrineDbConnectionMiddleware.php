<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Messenger;

use Doctrine\DBAL\Connection;
use EonX\EasyCore\Doctrine\DBAL\ConnectionChecker;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

/**
 * @deprecated since 3.0.19, will be removed in 4.0.
 * Use EonX\EasyAsync\Bridge\Symfony\Messenger\DoctrineManagersSanityCheckMiddleware instead.
 */
final class CheckDoctrineDbConnectionMiddleware implements MiddlewareInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        ConnectionChecker::checkConnection($this->connection);

        return $stack->next()
            ->handle($envelope, $stack);
    }
}
