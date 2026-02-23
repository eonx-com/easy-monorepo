<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Tests\Fixture\App\Entity\Article;
use EonX\EasyActivity\Tests\Fixture\App\Entity\Author;

/**
 * @see \EonX\EasyActivity\Tests\Unit\Common\Factory\ActivityLogEntryFactoryTest::testCreateSucceedsWithRelatedObjectsWhenConfiguredNestedObjectAllowedProperties
 */
return App::config([
    'easy_activity' => [
        'subjects' => [
            Article::class => [
                'allowed_properties' => [
                    'title',
                    'author',
                ],
                'nested_object_allowed_properties' => [
                    Author::class => [
                        'name',
                        'position',
                    ],
                ],
            ],
        ],
    ],
]);
