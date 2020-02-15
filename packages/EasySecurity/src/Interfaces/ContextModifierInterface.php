<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

use Symfony\Component\HttpFoundation\Request;

interface ContextModifierInterface
{
    /**
     * Get modifier priority.
     *
     * @return int
     */
    public function getPriority(): int;

    /**
     * Modify given context for given request.
     *
     * @param \EonX\EasySecurity\Interfaces\ContextInterface $context
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return void
     */
    public function modify(ContextInterface $context, Request $request): void;
}
