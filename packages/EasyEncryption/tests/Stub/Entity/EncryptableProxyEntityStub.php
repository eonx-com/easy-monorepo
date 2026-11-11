<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Stub\Entity;

/**
 * Like a Doctrine class-based lazy proxy: a subclass that declares nothing, so static::class is this class
 */
final class EncryptableProxyEntityStub extends AbstractEncryptableParentEntityStub {}
