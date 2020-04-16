<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Stubs;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\AbstractSimpleDataPersister;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Traits\RequestAttributesAwareTrait;

final class RequestAttributesAwareStub extends AbstractSimpleDataPersister
{
    use RequestAttributesAwareTrait;

    /**
     * @var mixed[]
     */
    private $requestAttributes;

    public function getApiResourceClass(): string
    {
        return '';
    }

    /**
     * @return null|mixed[]
     */
    public function getRequestAttributes(): ?array
    {
        return $this->requestAttributes;
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    public function persist($data)
    {
        return $data;
    }

    public function setRequestAttributes(string $param1): void
    {
        $this->requestAttributes = \func_get_args();
    }
}
