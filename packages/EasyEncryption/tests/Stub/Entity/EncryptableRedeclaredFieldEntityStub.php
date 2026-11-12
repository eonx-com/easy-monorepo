<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Stub\Entity;

use EonX\EasyEncryption\Encryptable\Attribute\EncryptableField;

/**
 * Redeclares the protected encryptable field of the parent: the same property, the child declaration wins
 */
final class EncryptableRedeclaredFieldEntityStub extends AbstractEncryptableProtectedFieldEntityStub
{
    #[EncryptableField(fieldName: 'child_code')]
    protected ?string $code = null;
}
