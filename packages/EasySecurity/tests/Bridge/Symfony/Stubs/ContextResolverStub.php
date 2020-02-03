<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony\Stubs;

use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\Resolvers\ContextResolverInterface;
use Symfony\Component\HttpFoundation\Request;

final class ContextResolverStub implements ContextResolverInterface
{
    /**
     * @var \EonX\EasySecurity\Interfaces\ContextInterface
     */
    private $context;

    /**
     * ContextResolverStub constructor.
     *
     * @param \EonX\EasySecurity\Interfaces\ContextInterface $context
     */
    public function __construct(ContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * Resolve context for given request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \EonX\EasySecurity\Interfaces\ContextInterface
     */
    public function resolve(Request $request): ContextInterface
    {
        return $this->context;
    }
}
