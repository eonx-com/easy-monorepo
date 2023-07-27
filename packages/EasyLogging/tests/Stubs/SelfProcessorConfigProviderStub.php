<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Stubs;

use EonX\EasyLogging\Config\AbstractSelfProcessorConfigProvider;

final class SelfProcessorConfigProviderStub extends AbstractSelfProcessorConfigProvider
{
    public function __invoke(array $records): array
    {
        return $records;
    }
}
