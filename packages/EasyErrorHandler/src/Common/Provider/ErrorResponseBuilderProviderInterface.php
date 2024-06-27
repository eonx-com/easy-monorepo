<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Provider;

interface ErrorResponseBuilderProviderInterface
{
    /**
     * @return iterable<\EonX\EasyErrorHandler\Common\Builder\ErrorResponseBuilderInterface>
     */
    public function getBuilders(): iterable;
}
