<?php
declare(strict_types=1);

namespace EonX\EasySwoole\EasyEncryption\Listener;

use EonX\EasyEncryption\AwsCloudHsm\Encryptor\AwsCloudHsmEncryptorInterface;
use EonX\EasySwoole\Common\Event\WorkerStartEvent;

final readonly class InitAwsCloudHsmEncryptorListener
{
    public function __construct(
        private AwsCloudHsmEncryptorInterface $encryptor,
    ) {
    }

    public function __invoke(WorkerStartEvent $event): void
    {
        $this->encryptor->init();
    }
}
