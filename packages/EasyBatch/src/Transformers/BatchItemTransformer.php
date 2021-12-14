<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Transformers;

use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Interfaces\SerializerInterface;
use EonX\EasyBatch\Objects\BatchItem;

final class BatchItemTransformer extends AbstractBatchObjectTransformer
{
    public function __construct(SerializerInterface $serializer, ?string $class = null, ?string $datetimeFormat = null)
    {
        parent::__construct($serializer, $class ?? BatchItem::class, $datetimeFormat);
    }

    /**
     * @return mixed[]
     */
    protected function doTransformToArray(BatchObjectInterface $batchObject): array
    {
        $array = parent::doTransformToArray($batchObject);

        if (isset($array['message'])) {
            $array['message'] = $this->serializer->serialize($array['message']);
        }

        return $array;
    }

    /**
     * @param \EonX\EasyBatch\Interfaces\BatchItemInterface $batchObject
     * @param mixed[] $data
     */
    protected function hydrateBatchObject(BatchObjectInterface $batchObject, array $data): void
    {
        $batchObject
            ->setApprovalRequired((bool)($data['requires_approval'] ?? 0))
            ->setAttempts((int)($data['attempts'] ?? 0))
            ->setBatchId((string)$data['batch_id']);

        if (isset($data['type']) === false) {
            $batchObject->setType(BatchItemInterface::TYPE_MESSAGE);
        }

        if (isset($data['message'])) {
            $batchObject->setMessage($this->serializer->unserialize((string)$data['message']));
        }

        if (isset($data['depends_on_name'])) {
            $batchObject->setDependsOnName((string)$data['depends_on_name']);
        }
    }
}
