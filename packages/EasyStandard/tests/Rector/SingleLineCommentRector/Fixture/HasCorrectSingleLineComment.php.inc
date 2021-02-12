<?php
declare(strict_types=1);

$factoryMuffin = new \stdClass();

// My single comment
$factoryMuffin->define(\stdClass::class)->setDefinitions([
    'bin' => $factoryMuffin->faker()->unique()->randomNumber(6, true),
]);
