<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyCore\Tests;

use org\bovigo\vfs\vfsStreamWrapper;

/**
 * Registers vfs: stream protocol.
 */
abstract class AbstractVfsTestCase extends AbstractTestCase
{
    /**
     * {@inheritdoc}
     *
     * @throws \org\bovigo\vfs\vfsStreamException
     */
    public function setUp()
    {
        vfsStreamWrapper::register();

        parent::setUp();
    }
}
