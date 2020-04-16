<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\Traits;

trait RequestAttributesAwareTrait
{
    /**
     * Using a trait for the feature allows us to enforce this method to be implemented by the classes using it
     * without impacting their inheritance and allowing them to modify the method signature accordingly to their needs.
     */
    abstract public function setRequestAttributes(): void;
}
