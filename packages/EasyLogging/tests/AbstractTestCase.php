<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractTestCase extends TestCase
{
    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $var = __DIR__ . '/../var';

        if ($fs->exists($var)) {
            $fs->remove($var);
        }

        parent::tearDown();
    }

    /**
     * Returns object's private property value.
     *
     * @param object $object
     * @param string $property
     *
     * @return mixed
     */
    protected function getPrivatePropertyValue($object, string $property)
    {
        return (function ($property) {
            return $this->{$property};
        })->call($object, $property);
    }
}
