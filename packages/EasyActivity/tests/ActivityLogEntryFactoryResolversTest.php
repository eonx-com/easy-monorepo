<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests;

use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\ActivitySubjectData;
use EonX\EasyActivity\Actor;
use EonX\EasyActivity\Interfaces\ActivitySubjectDataInterface;
use EonX\EasyActivity\Interfaces\ActivitySubjectDataResolverInterface;
use EonX\EasyActivity\Interfaces\ActivitySubjectInterface;
use EonX\EasyActivity\Interfaces\ActorInterface;
use EonX\EasyActivity\Interfaces\ActorResolverInterface;
use EonX\EasyActivity\Tests\Fixtures\ActivityLogEntity;
use EonX\EasyActivity\Tests\Fixtures\Author;
use EonX\EasyActivity\Tests\Stubs\ActivityLogFactoryStub;

final class ActivityLogEntryFactoryResolversTest extends AbstractTestCase
{
    public function testCreateSucceedsWithCustomActorResolver(): void
    {
        $factory = new ActivityLogFactoryStub(
            [
                Author::class => [],
            ],
            [],
            new class() implements ActorResolverInterface {
                public function resolve(object $object): ActorInterface
                {
                    return new Actor(
                        'custom-actor-type',
                        'custom-actor-id',
                        'custom-actor-name'
                    );
                }
            }
        );
        $author = new Author();
        $author->setId(1);

        $result = $factory->create(
            ActivityLogEntry::ACTION_UPDATE,
            $author,
            ['change' => [null, 1]]
        );

        /** @var \EonX\EasyActivity\ActivityLogEntry $result */
        self::assertNotNull($result);
        self::assertSame('custom-actor-id', $result->getActorId());
        self::assertSame('custom-actor-type', $result->getActorType());
        self::assertSame('custom-actor-name', $result->getActorName());
    }

    public function testCreateSucceedsWithCustomSubjectDataResolver(): void
    {
        $factory = new ActivityLogFactoryStub(
            [
                Author::class => [],
            ],
            [],
            null,
            null,
            new class() implements ActivitySubjectDataResolverInterface {
                public function resolve(
                    string $action,
                    ActivitySubjectInterface $subject,
                    array $changeSet
                ): ?ActivitySubjectDataInterface {
                    $data = [];
                    $oldData = [];
                    foreach ($changeSet as $key => [$newValue, $oldValue]) {
                        $data[$key] = $newValue;
                        $oldData[$key] = $oldValue;
                    }

                    return new ActivitySubjectData(\serialize($data), \serialize($oldData));
                }
            }
        );
        $author = new Author();
        $author->setId(1);

        $result = $factory->create(
            ActivityLogEntry::ACTION_UPDATE,
            $author,
            ['field' => [1, 2]]
        );

        /** @var \EonX\EasyActivity\ActivityLogEntry $result */
        self::assertNotNull($result);
        self::assertSame('a:1:{s:5:"field";i:1;}', $result->getSubjectData());
        self::assertSame('a:1:{s:5:"field";i:2;}', $result->getSubjectOldData());
    }

    public function testCreateSucceedsWithObjectThatImplementsSubjectInterface(): void
    {
        $factory = new ActivityLogFactoryStub([], []);
        $subjectId = 'subject-id';
        $subjectType = 'subject-type';
        $activityLogEntity = new ActivityLogEntity($subjectId, $subjectType, ['field1']);

        $result = $factory->create(
            ActivityLogEntry::ACTION_UPDATE,
            $activityLogEntity,
            [
                'field1' => [1, 2],
                'field2' => [2, 3],
            ]
        );

        /** @var \EonX\EasyActivity\ActivityLogEntry $result */
        self::assertNotNull($result);
        self::assertSame($subjectId, $result->getSubjectId());
        self::assertSame($subjectType, $result->getSubjectType());
        self::assertSame('{"field1":2}', $result->getSubjectData());
        self::assertSame('{"field1":1}', $result->getSubjectOldData());
    }
}
