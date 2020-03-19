<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Symfony\Factories;

use EonX\EasyPagination\Interfaces\StartSizeDataInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataResolverInterface;
use Psr\Http\Message\ServerRequestInterface;

final class StartSizeDataFactory
{
    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private $request;

    /**
     * @var \EonX\EasyPagination\Interfaces\StartSizeDataResolverInterface
     */
    private $resolver;

    public function __construct(ServerRequestInterface $request, StartSizeDataResolverInterface $resolver)
    {
        $this->request = $request;
        $this->resolver = $resolver;
    }

    public function __invoke(): StartSizeDataInterface
    {
        return $this->resolver->resolve($this->request);
    }
}
