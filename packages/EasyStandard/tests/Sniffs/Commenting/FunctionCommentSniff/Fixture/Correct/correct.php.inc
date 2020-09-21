<?php

trait TraitWithAbstractMethod
{
    /**
     * Do something from trait.
     *
     * @param mixed[] $param
     *
     * @return mixed[]
     */
    abstract public function doSomethingFromTrait(array $param): array;
}

abstract class AbstractClassWithTrait
{
    use \TraitWithAbstractMethod;
}

class MyAnnotationClass extends \AbstractClassWithTrait
{
    /**
     * Do something.
     *
     * @param string $param
     *
     * @return void
     */
    public function doSomething(string $param): void
    {

    }

    /**
     * {@inheritdoc}
     */
    public function doSomethingFromTrait(array $param): array
    {
        return $param;
    }
}
