<?php
declare(strict_types=1);

namespace EonX\EasySwoole\EasyEncryption\Listener;

use EonX\EasyEncryption\AwsCloudHsm\Encryptor\AwsCloudHsmEncryptorInterface;
use EonX\EasyEncryption\Common\Encryptor\EncryptorInterface;
use EonX\EasySwoole\Common\Event\WorkerStartEvent;

final readonly class InitAwsCloudHsmEncryptorListener
{
    public function __construct(
        private EncryptorInterface $encryptor,
    ) {
    }

    public function __invoke(WorkerStartEvent $event): void
    {
        if ($this->encryptor instanceof AwsCloudHsmEncryptorInterface) {
            $this->encryptor->init();
        }
    }
}
