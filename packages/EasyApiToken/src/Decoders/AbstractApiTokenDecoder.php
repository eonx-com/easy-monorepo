<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Decoders;

use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;

abstract class AbstractApiTokenDecoder implements ApiTokenDecoderInterface
{
    /**
     * @var null|string
     */
    private $name;

    public function __construct(?string $name = null)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name ?? self::class;
    }
}
