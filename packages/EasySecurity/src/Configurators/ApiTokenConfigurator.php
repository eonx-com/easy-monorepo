<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Configurators;

use EonX\EasyApiToken\Interfaces\Factories\ApiTokenDecoderFactoryInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;

final class ApiTokenConfigurator extends AbstractSecurityContextConfigurator
{
    public function __construct(
        private readonly ApiTokenDecoderFactoryInterface $apiTokenDecoderFactory,
        private readonly ?string $apiTokenDecoder = null,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function configure(SecurityContextInterface $context, Request $request): void
    {
        $decoder = $this->apiTokenDecoderFactory->build($this->apiTokenDecoder);

        $context->setToken($decoder->decode($request));
    }
}
