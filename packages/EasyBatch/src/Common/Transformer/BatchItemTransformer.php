<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Transformer;

use EonX\EasyBatch\Common\Enum\BatchItemType;
use EonX\EasyBatch\Common\Exception\BatchItemCannotBeEncryptedException;
use EonX\EasyBatch\Common\Serializer\MessageSerializerInterface;
use EonX\EasyBatch\Common\ValueObject\BatchObjectInterface;
use EonX\EasyEncryption\Common\Encryptor\EncryptorInterface;

final class BatchItemTransformer extends AbstractBatchObjectTransformer
{
    private ?EncryptorInterface $encryptor = null;

    public function __construct(
        private readonly MessageSerializerInterface $messageSerializer,
        string $class,
        string $dateTimeFormat,
    ) {
        parent::__construct($class, $dateTimeFormat);
    }

    public function setEncryptor(EncryptorInterface $encryptor): void
    {
        $this->encryptor = $encryptor;
    }

    /**
     * @param \EonX\EasyBatch\Common\ValueObject\BatchItemInterface $batchObject
     *
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemCannotBeEncryptedException
     */
    protected function doTransformToArray(BatchObjectInterface $batchObject): array
    {
        $array = parent::doTransformToArray($batchObject);

        if (isset($array['message'])) {
            $message = $this->messageSerializer->serialize($array['message']);

            if ($batchObject->isEncrypted()) {
                $message = $this->getEncryptor()
                    ->encrypt($message, $batchObject->getEncryptionKeyName());
            }

            $array['message'] = $message;
        }

        return $array;
    }

    /**
     * @param \EonX\EasyBatch\Common\ValueObject\BatchItemInterface $batchObject
     *
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemCannotBeEncryptedException
     */
    protected function hydrateBatchObject(BatchObjectInterface $batchObject, array $data): void
    {
        $batchObject
            ->setAttempts((int)($data['attempts'] ?? 0))
            ->setBatchId((string)$data['batch_id'])
            ->setEncrypted((bool)($data['encrypted'] ?? 0))
            ->setMaxAttempts((int)($data['max_attempts'] ?? 1));

        if (isset($data['type']) === false) {
            $batchObject->setType(BatchItemType::Message->value);
        }

        if (isset($data['message'])) {
            $message = (string)$data['message'];

            if (((bool)($data['encrypted'] ?? false)) === true) {
                $decrypted = $this->getEncryptor()
                    ->decrypt($message);
                $message = $decrypted->getRawDecryptedString();

                $batchObject->setEncryptionKeyName($decrypted->getKeyName());
            }

            $batchObject->setMessage($this->messageSerializer->unserialize($message));
        }

        if (isset($data['depends_on_name'])) {
            $batchObject->setDependsOnName((string)$data['depends_on_name']);
        }
    }

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchItemCannotBeEncryptedException
     */
    private function getEncryptor(): EncryptorInterface
    {
        if ($this->encryptor !== null) {
            return $this->encryptor;
        }

        throw new BatchItemCannotBeEncryptedException(
            'In order to use message encryption feature you must install eonx-com/easy-encryption'
        );
    }
}
