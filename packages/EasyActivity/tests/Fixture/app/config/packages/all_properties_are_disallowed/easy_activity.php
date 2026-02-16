<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Tests\Fixture\App\Entity\Article;

/**
 * @see \EonX\EasyActivity\Tests\Unit\EasyDoctrine\Subscriber\EasyDoctrineEntityEventsSubscriberTest::provideProperties
 */
return App::config([
    'easy_activity' => [
        'subjects' => [
            Article::class => [
                'disallowed_properties' => [
                    'title',
                    'createdAt',
                    'author',
                    'content',
                    'id',
                ],
            ],
        ],
    ],
]);
