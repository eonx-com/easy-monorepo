<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Stubs;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\AbstractSimpleDataPersister;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces\RequestAttributesAwareInterface;

final class RequestAttributesAwareStub extends AbstractSimpleDataPersister implements RequestAttributesAwareInterface
{
    /**
     * @var mixed[]
     */
    private $requestAttributes;

    /**
     * @var string
     */
    private $setter;

    public function __construct(?string $setter = null)
    {
        $this->setter = $setter ?? 'setRequestAttributes';
    }

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

    public function getRequestAttributesSetter(): string
    {
        return $this->setter;
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
