<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Modifiers;

use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\ProviderProviderInterface;
use Symfony\Component\HttpFoundation\Request;

final class ProviderFromHeaderModifier extends AbstractContextModifier
{
    /**
     * @var string[]
     */
    private $headerNames;

    /**
     * @var string
     */
    private $permission;

    /**
     * @var \EonX\EasySecurity\Interfaces\ProviderProviderInterface
     */
    private $providerProvider;

    /**
     * @param null|string|string[] $headerNames
     */
    public function __construct(
        ProviderProviderInterface $providerProvider,
        ?int $priority = null,
        $headerNames = null,
        ?string $permission = null
    ) {
        $this->providerProvider = $providerProvider;
        $this->headerNames = (array)($headerNames ?? ['Provider-Id', 'X-Provider-Id']);
        $this->permission = $permission ?? 'provider:switch';

        parent::__construct($priority);
    }

    public function modify(ContextInterface $context, Request $request): void
    {
        $header = $this->getHeaderValue($request);

        // If header empty, skip
        if (empty($header)) {
            return;
        }

        // If current context hasn't required permission, skip
        if ($context->hasPermission($this->permission) === false) {
            return;
        }

        $context->setProvider($this->providerProvider->getProvider($header));
    }

    private function getHeaderValue(Request $request): ?string
    {
        foreach ($this->headerNames as $headerName) {
            $header = $request->headers->get($headerName);

            if (empty($header) === false) {
                return $header;
            }
        }

        return null;
    }
}
