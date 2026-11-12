<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Stub\Entity;

use EonX\EasyEncryption\Encryptable\Attribute\EncryptableField;

/**
 * Base class with a protected encryptable field, see EncryptableRedeclaredFieldEntityStub
 */
abstract class AbstractEncryptableProtectedFieldEntityStub
{
    #[EncryptableField(fieldName: 'parent_code')]
    protected ?string $code = null;
}
