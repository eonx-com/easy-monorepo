<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Encryptable\Encryptable;

use EonX\EasyEncryption\Encryptable\Metadata\EncryptableMetadata;
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
            // Through reflection: a private property of a parent class is not reachable from the trait scope
            // when the trait is used in a child class
            $reflectionProperty = EncryptableMetadata::getProperty(static::class, $entityPropertyName);

            if ($reflectionProperty === null || \array_key_exists($fieldName, $decryptedData) === false) {
                continue;
            }

            $reflectionProperty->setValue($this, $decryptedData[$fieldName]);
        }
    }

    /**
     * @param callable(string): \EonX\EasyEncryption\Encryptable\ValueObject\EncryptedString $encryptor
     * @param callable(string, string, string): string $hashCalculator
     */
    public function encrypt(callable $encryptor, callable $hashCalculator): void
    {
        $metadata = new EncryptableMetadata();
        $rawData = [];

        foreach ($metadata->getEncryptableFieldNames(static::class) as $entityPropertyName => $fieldName) {
            // Through reflection: a private property of a parent class is not reachable from the trait scope
            // when the trait is used in a child class
            $reflectionProperty = EncryptableMetadata::getProperty(static::class, $entityPropertyName);

            if ($reflectionProperty === null) {
                continue;
            }

            $value = $reflectionProperty->getValue($this);

            if ($value !== null && \is_string($value) === false) {
                throw new UnexpectedValueException(
                    \sprintf(
                        'The value of the property "%s" in the entity "%s" must be a string or null, but it is "%s".',
                        $entityPropertyName,
                        static::class,
                        \get_debug_type($value)
                    )
                );
            }

            $rawData[$fieldName] = $value;

            if ($value !== null) {
                $reflectionProperty->setValue($this, $hashCalculator(static::class, $entityPropertyName, $value));
            }
        }

        $encryptedText = $encryptor((string)\json_encode($rawData));

        $this->encryptedData = $encryptedText->value;
        $this->encryptionKeyName = $encryptedText->encryptionKeyName;
    }
}
