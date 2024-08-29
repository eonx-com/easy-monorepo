<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Resolver;

use EonX\EasyActivity\Common\Entity\ActivitySubjectInterface;
use EonX\EasyActivity\Common\Enum\ActivityAction;
use EonX\EasyActivity\Common\Serializer\ActivitySubjectDataSerializerInterface;
use EonX\EasyActivity\Common\ValueObject\ActivitySubjectData;

final readonly class DefaultActivitySubjectDataResolver implements ActivitySubjectDataResolverInterface
{
    public const CONTEXT_SERIALIZE_LOG_DATA_KEY = 'easy_activity_serialize_log_data';

    public const CONTEXT_SERIALIZE_LOG_DATA_NEW = 'new';

    public const CONTEXT_SERIALIZE_LOG_DATA_OLD = 'old';

    public function __construct(
        private ActivitySubjectDataSerializerInterface $serializer,
    ) {
    }

    public function resolve(
        ActivityAction|string $action,
        ActivitySubjectInterface $subject,
        array $changeSet,
    ): ?ActivitySubjectData {
        [$oldData, $data] = $this->resolveChangeData($action, $changeSet);

        $serializedData = $data !== null
            ? $this->serializer->serialize($data, $subject, [
                self::CONTEXT_SERIALIZE_LOG_DATA_KEY => self::CONTEXT_SERIALIZE_LOG_DATA_NEW,
            ])
            : null;
        $serializedOldData = $oldData !== null
            ? $this->serializer->serialize($oldData, $subject, [
                self::CONTEXT_SERIALIZE_LOG_DATA_KEY => self::CONTEXT_SERIALIZE_LOG_DATA_OLD,
            ])
            : null;

        if ($serializedData === null && $serializedOldData === null) {
            return null;
        }

        return new ActivitySubjectData($serializedData, $serializedOldData);
    }

    private function resolveChangeData(ActivityAction|string $action, array $changeSet): array
    {
        $oldData = [];
        $data = [];
        foreach ($changeSet as $field => [$oldValue, $newValue]) {
            $data[$field] = $newValue;
            $oldData[$field] = $oldValue;
        }

        if ($action === ActivityAction::Create) {
            $oldData = null;
        }

        if ($action === ActivityAction::Delete) {
            $data = null;
        }

        return [$oldData, $data];
    }
}
