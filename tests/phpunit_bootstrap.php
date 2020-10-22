<?php

declare(strict_types=1);

require __DIR__ . '/rector_bootstrap.php';

// enables mocking of final classes
// @see https://tomasvotruba.com/blog/2019/03/28/how-to-mock-final-classes-in-phpunit/
DG\BypassFinals::enable();
