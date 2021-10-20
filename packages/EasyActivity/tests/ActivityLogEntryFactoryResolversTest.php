<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests;

use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\Actor;
use EonX\EasyActivity\Interfaces\ActorInterface;
use EonX\EasyActivity\Interfaces\ActorResolverInterface;
use EonX\EasyActivity\Interfaces\SubjectInterface;
use EonX\EasyActivity\Interfaces\SubjectResolverInterface;
use EonX\EasyActivity\Subject;
use EonX\EasyActivity\Tests\Fixtures\ActivityLogEntity;
use EonX\EasyActivity\Tests\Fixtures\ActivityLogEntityInterface;
use EonX\EasyActivity\Tests\Fixtures\Author;
use EonX\EasyActivity\Tests\Stubs\ActivityLogFactoryStub;
use RuntimeException;

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
                public function resolveActor(object $object): ActorInterface
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
            ['change' => 1]
        );

        /** @var \EonX\EasyActivity\ActivityLogEntry $result */
        self::assertNotNull($result);
        self::assertSame('custom-actor-id', $result->getActorId());
        self::assertSame('custom-actor-type', $result->getActorType());
        self::assertSame('custom-actor-name', $result->getActorName());
    }

    public function testCreateSucceedsWithCustomSubjectResolver(): void
    {
        $factory = new ActivityLogFactoryStub(
            [], // ignore config option `easy_activity.subjects`
            [], // ignore config option `easy_activity.disallowed_properties`
            null,
            new class() implements SubjectResolverInterface {
                public function resolveSubject(string $action, object $object, array $changeSet): SubjectInterface
                {
                    if ($object instanceof ActivityLogEntityInterface === false) {
                        throw new RuntimeException('Wrong argument');
                    }
                    foreach ($changeSet as $property => $value) {
                        if (\in_array($property, $object->getActivityLoggableProperties(), true) === false) {
                            unset($changeSet[$property]);
                        }
                    }

                    return new Subject(
                        $object->getActivitySubjectId(),
                        $object->getActivitySubjectType(),
                        (string)\json_encode($changeSet),
                        (string)\json_encode($changeSet)
                    );
                }
            }
        );
        $activityLogEntity = new ActivityLogEntity('11', 'p1', 'p2', 'p3');

        $result = $factory->create(
            ActivityLogEntry::ACTION_UPDATE,
            $activityLogEntity,
            [
                'property1' => 'p11',
                'property2' => 'p22',
                'property3' => 'p33',
            ]
        );

        /** @var \EonX\EasyActivity\ActivityLogEntry $result */
        self::assertNotNull($result);
        self::assertSame($activityLogEntity->getActivitySubjectId(), $result->getSubjectId());
        self::assertSame($activityLogEntity->getActivitySubjectType(), $result->getSubjectType());
        self::assertSame('{"property1":"p11","property2":"p22"}', $result->getData());
        self::assertSame('{"property1":"p11","property2":"p22"}', $result->getOldData());
    }
}
