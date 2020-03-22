<?php

declare(strict_types=1);

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

include __DIR__ . '/../vendor/autoload.php';

/**
 * @coversNothing
 */

// Until Doctrine Annotations v2.0, we need to register an autoloader, which is just 'class_exists'.
/** @noinspection PhpDeprecationInspection Will be removed with doctrine annotations v2.0 */
AnnotationRegistry::registerUniqueLoader('class_exists');

// Ignore @covers and @coversNothing annotations
AnnotationReader::addGlobalIgnoredName('covers');
AnnotationReader::addGlobalIgnoredName('coversNothing');
