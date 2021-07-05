<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Transformers;

use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Objects\BatchItem;

final class BatchItemTransformer extends AbstractBatchObjectTransformer
{
    public function __construct(?string $class = null, ?string $datetimeFormat = null)
    {
        parent::__construct($class ?? BatchItem::class, $datetimeFormat);
    }

    /**
     * @return mixed[]
     */
    protected function doTransformToArray(BatchObjectInterface $batchObject): array
    {
        $array = parent::doTransformToArray($batchObject);

        if (isset($array['message'])) {
            $array['message'] = $this->serialize($array['message']);
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
            ->setBatchId((string)$data['batch_id'])
            ->setType((string)($data['type'] ?? BatchItemInterface::TYPE_MESSAGE))
            ->setStatus((string)($data['status'] ?? BatchItemInterface::STATUS_PENDING))
            ->setId($data['id']);

        if (isset($data['message'])) {
            $batchObject->setMessage($this->unserialize((string)$data['message']));
        }

        if (isset($data['name'])) {
            $batchObject->setName((string)$data['name']);
        }

        if (isset($data['depends_on_name'])) {
            $batchObject->setDependsOnName((string)$data['depends_on_name']);
        }
    }

    private function serialize(object $message): string
    {
        $body = \addslashes(\serialize($message));

        if (\preg_match('//u', $body) === false) {
            $body = \base64_encode($body);
        }

        return $body;
    }

    private function unserialize(string $message): object
    {
        if (\strpos($message, '}', -1) === false) {
            $message = \base64_decode($message);
        }

        return \unserialize(\stripslashes($message));
    }
}
