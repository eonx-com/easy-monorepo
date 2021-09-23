<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Stubs;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\AbstractSimpleDataPersister;

abstract class AbstractRequestAttributesAwareStub extends AbstractSimpleDataPersister
{
    /**
     * @var mixed[]
     */
    protected $requestAttributes;

    public function setRequestAttributes(string $param1): void
    {
        $this->requestAttributes = \func_get_args();
    }
}
