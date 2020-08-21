<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Decoders;

use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use Nette\Utils\Strings;
use Symfony\Component\HttpFoundation\Request;

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

    protected function getHeaderWithoutPrefix(string $header, string $prefix, Request $request): ?string
    {
        $header = $request->headers->get($header, '');

        if (Strings::startsWith($header, $prefix) === false) {
            return null;
        }

        return \substr((string)$header, \strlen($prefix));
    }
}
