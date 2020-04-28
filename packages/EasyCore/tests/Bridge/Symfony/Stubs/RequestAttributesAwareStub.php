<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Stubs;

final class RequestAttributesAwareStub extends AbstractRequestAttributesAwareStub
{
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
     * @param null|mixed[] $context
     *
     * @return mixed
     */
    public function persist($data, ?array $context = null)
    {
        return $data;
    }
}
