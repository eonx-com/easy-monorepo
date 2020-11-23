<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Stores;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyWebhook\Interfaces\IdAwareWebhookResultStoreInterface;

abstract class AbstractIdAwareWebhookResultStore implements IdAwareWebhookResultStoreInterface
{
    /**
     * @var \EonX\EasyRandom\Interfaces\RandomGeneratorInterface
     */
    private $random;

    public function __construct(RandomGeneratorInterface $random)
    {
        $this->random = $random;
    }

    public function generateWebhookId(): string
    {
        return $this->random->uuidV4();
    }
}
