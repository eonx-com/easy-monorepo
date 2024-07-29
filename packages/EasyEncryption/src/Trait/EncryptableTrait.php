<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Trait;

use EonX\EasyEncryption\Metadata\EncryptableMetadata;
use UnexpectedValueException;

trait EncryptableTrait
{
    protected string $encryptedData;

    protected string $encryptionKeyName;

    public function decrypt(callable $decryptor): void
    {
        if (isset($this->encryptedData) === false) {
            return;
        }

        $decryptedString = $decryptor($this->encryptedData);
        $decryptedData = \json_decode((string)$decryptedString, true);

        if (\is_array($decryptedData) === false) {
            throw new UnexpectedValueException('Decrypted data has to be an array.');
        }

        $metadata = new EncryptableMetadata();

        foreach ($metadata->getEncryptableFieldNames(static::class) as $entityPropertyName => $fieldName) {
            if (
                \property_exists($this, $entityPropertyName) === false ||
                \array_key_exists($fieldName, $decryptedData) === false
            ) {
                continue;
            }

            $this->{$entityPropertyName} = $decryptedData[$fieldName];
        }
    }

    /**
     * @param callable(string): \EonX\EasyEncryption\ValueObject\EncryptedText $encryptor
     * @param callable(string): string $hashCalculator
     */
    public function encrypt(callable $encryptor, callable $hashCalculator): void
    {
        $metadata = new EncryptableMetadata();
        $rawData = [];

        foreach ($metadata->getEncryptableFieldNames(static::class) as $entityPropertyName => $fieldName) {
            if ($this->{$entityPropertyName} !== null && \is_string($this->{$entityPropertyName}) === false) {
                throw new UnexpectedValueException(\sprintf(
                    'The value of the property "%s" in the entity "%s" must be a string or null, but it is "%s".',
                    $entityPropertyName,
                    static::class,
                    \get_debug_type($this->{$entityPropertyName})
                ));
            }

            $rawData[$fieldName] = $this->{$entityPropertyName};

            if ($this->{$entityPropertyName} !== null) {
                $this->{$entityPropertyName} = $hashCalculator($this->{$entityPropertyName});
            }
        }

        $encryptedText = $encryptor((string)\json_encode($rawData));

        $this->encryptedData = $encryptedText->value;
        $this->encryptionKeyName = $encryptedText->encryptionKeyName;
    }
}
