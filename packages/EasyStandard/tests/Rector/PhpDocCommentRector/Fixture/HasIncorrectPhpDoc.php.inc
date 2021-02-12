<?php
declare(strict_types=1);

use App\ApiResource\GlobalSafeBin;

/** @var \App\Tests\FactoryMuffinWrapper $factoryMuffin My comments. */
$factoryMuffin->define(GlobalSafeBin::class)->setDefinitions([
    'bin' => $factoryMuffin->faker()->unique()->randomNumber(6, true),
]);

?>

-----
<?php
declare(strict_types=1);

use App\ApiResource\GlobalSafeBin;

/** @var \App\Tests\FactoryMuffinWrapper $factoryMuffin my comments */
$factoryMuffin->define(GlobalSafeBin::class)->setDefinitions([
    'bin' => $factoryMuffin->faker()->unique()->randomNumber(6, true),
]);

?>
