<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

interface ErrorResponseBuilderProviderInterface
{
    /**
     * @return iterable<\EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface>
     */
    public function getBuilders(): iterable;
}
