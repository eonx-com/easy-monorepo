<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Configurators;

use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;

final class ApiTokenConfigurator extends AbstractSecurityContextConfigurator
{
    /**
     * @var \EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface
     */
    private $apiTokenDecoder;

    public function __construct(ApiTokenDecoderInterface $apiTokenDecoder, ?int $priority = null)
    {
        $this->apiTokenDecoder = $apiTokenDecoder;

        parent::__construct($priority);
    }

    public function configure(SecurityContextInterface $context, Request $request): void
    {
        $context->setToken($this->apiTokenDecoder->decode($request));
    }
}
