<?php

declare(strict_types=1);

namespace EonX\EasyPagination;

use EonX\EasyPagination\Exceptions\NoPaginationResolverSetException;
use EonX\EasyPagination\Interfaces\PaginationConfigInterface;
use EonX\EasyPagination\Interfaces\PaginationInterface;
use EonX\EasyPagination\Interfaces\PaginationProviderInterface;

final class PaginationProvider implements PaginationProviderInterface
{
    /**
     * @var \EonX\EasyPagination\Interfaces\PaginationConfigInterface
     */
    private $config;

    /**
     * @var null|\EonX\EasyPagination\Interfaces\PaginationInterface
     */
    private $pagination;

    /**
     * @var callable
     */
    private $resolver;

    public function __construct(PaginationConfigInterface $config)
    {
        $this->config = $config;
    }

    public function getPagination(): PaginationInterface
    {
        if ($this->pagination !== null) {
            return $this->pagination;
        }

        if ($this->resolver !== null) {
            return $this->pagination = \call_user_func($this->resolver);
        }

        throw new NoPaginationResolverSetException(\sprintf(
            'No pagination resolver set on provider. Use %s::setResolver().',
            PaginationProviderInterface::class
        ));
    }

    public function getPaginationConfig(): PaginationConfigInterface
    {
        return $this->config;
    }

    public function setResolver(callable $resolver): PaginationProviderInterface
    {
        $this->resolver = $resolver;
        $this->pagination = null;

        return $this;
    }
}
