<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests;

use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\Interfaces\ActivityLogEntryFactoryInterface;
use EonX\EasyActivity\Tests\Bridge\Symfony\Fixtures\App\Entity\Author;
use EonX\EasyActivity\Tests\Fixtures\ActivityLogEntity;
use Symfony\Component\Uid\NilUuid;

final class ActivityLogEntryFactoryResolversTest extends AbstractTestCase
{
    /**
     * @see packages/EasyActivity/tests/Bridge/Symfony/Fixtures/app/config/packages/custom_activity_subject_data_resolver
     */
    public function testCreateSucceedsWithCustomSubjectDataResolver(): void
    {
        self::bootKernel(['environment' => 'custom_activity_subject_data_resolver']);
        $author = new Author();
        $author->setId((string)(new NilUuid()));
        $sut = self::getService(ActivityLogEntryFactoryInterface::class);

        $result = $sut->create(
            ActivityLogEntry::ACTION_UPDATE,
            $author,
            ['field' => [1, 2]]
        );

        self::assertNotNull($result);
        self::assertSame('a:1:{s:5:"field";i:1;}', $result->getSubjectData());
        self::assertSame('a:1:{s:5:"field";i:2;}', $result->getSubjectOldData());
    }

    public function testCreateSucceedsWithObjectThatImplementsSubjectInterface(): void
    {
        $subjectId = 'subject-id';
        $subjectType = 'subject-type';
        $activityLogEntity = new ActivityLogEntity($subjectId, $subjectType, ['field1']);
        $sut = self::getService(ActivityLogEntryFactoryInterface::class);

        $result = $sut->create(
            ActivityLogEntry::ACTION_UPDATE,
            $activityLogEntity,
            [
                'field1' => [1, 2],
                'field2' => [2, 3],
            ]
        );

        self::assertNotNull($result);
        self::assertSame($subjectId, $result->getSubjectId());
        self::assertSame($subjectType, $result->getSubjectType());
        self::assertSame('{"field1":2}', $result->getSubjectData());
        self::assertSame('{"field1":1}', $result->getSubjectOldData());
    }
}
