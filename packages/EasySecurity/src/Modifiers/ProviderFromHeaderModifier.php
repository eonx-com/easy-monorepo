<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Modifiers;

use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\ProviderProviderInterface;
use Symfony\Component\HttpFoundation\Request;

final class ProviderFromHeaderModifier extends AbstractContextModifier
{
    /**
     * @var string
     */
    private $headerName;

    /**
     * @var string
     */
    private $permission;

    /**
     * @var \EonX\EasySecurity\Interfaces\ProviderProviderInterface
     */
    private $providerProvider;

    /**
     * ProviderFromHeaderDataResolver constructor.
     *
     * @param \EonX\EasySecurity\Interfaces\ProviderProviderInterface $providerProvider
     * @param null|int $priority
     * @param null|string $headerName
     * @param null|string $permission
     */
    public function __construct(
        ProviderProviderInterface $providerProvider,
        ?int $priority = null,
        ?string $headerName = null,
        ?string $permission = null
    ) {
        $this->providerProvider = $providerProvider;
        $this->headerName = $headerName ?? 'X-Provider-Id';
        $this->permission = $permission ?? 'provider:switch';

        parent::__construct($priority);
    }

    /**
     * Modify given context for given request.
     *
     * @param \EonX\EasySecurity\Interfaces\ContextInterface $context
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return void
     */
    public function modify(ContextInterface $context, Request $request): void
    {
        $header = $request->headers->get($this->headerName);

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
}
