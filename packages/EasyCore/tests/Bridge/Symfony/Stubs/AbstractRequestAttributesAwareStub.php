<?php
declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Stubs;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\AbstractSimpleDataPersister;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Traits\RequestAttributesAwareTrait;

abstract class AbstractRequestAttributesAwareStub extends AbstractSimpleDataPersister
{
    use RequestAttributesAwareTrait;

    /**
     * @var mixed[]
     */
    protected $requestAttributes;

    public function setRequestAttributes(string $param1): void
    {
        $this->requestAttributes = \func_get_args();
    }
}
