<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony\Stubs;

use EonX\EasySecurity\Bridge\Symfony\Interfaces\DeferredContextAwareInterface;
use EonX\EasySecurity\Bridge\Symfony\Traits\DeferredContextAwareTrait;
use EonX\EasySecurity\Interfaces\ContextInterface;

final class DeferredContextAwareTraitStub implements DeferredContextAwareInterface
{
    use DeferredContextAwareTrait;

    public function getContext(): ContextInterface
    {
        return $this->resolveContext();
    }
}
