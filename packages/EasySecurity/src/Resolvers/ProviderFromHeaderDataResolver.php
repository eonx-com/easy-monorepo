<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Resolvers;

use EonX\EasySecurity\Context;
use EonX\EasySecurity\Interfaces\ProviderProviderInterface;
use EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface;

final class ProviderFromHeaderDataResolver extends AbstractContextDataResolver
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
     * Resolve context data.
     *
     * @param \EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface $data
     *
     * @return \EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface
     */
    public function resolve(ContextResolvingDataInterface $data): ContextResolvingDataInterface
    {
        $header = $data->getRequest()->headers->get($this->headerName);

        // If header empty, skip
        if (empty($header)) {
            return $data;
        }

        // If current context hasn't required permission, skip
        if ((new Context($data->getRoles()))->hasPermission($this->permission) === false) {
            return $data;
        }

        $data->setProvider($this->providerProvider->getProvider($header));

        return $data;
    }
}
