<?php
declare(strict_types=1);

use App\ApiResource\GlobalSafeBin;

//      my single comment!
$factoryMuffin->define(GlobalSafeBin::class)->setDefinitions([
    'bin' => $factoryMuffin->faker()->unique()->randomNumber(6, true),
]);

?>

-----
<?php
declare(strict_types=1);

use App\ApiResource\GlobalSafeBin;

// My single comment
$factoryMuffin->define(GlobalSafeBin::class)->setDefinitions([
    'bin' => $factoryMuffin->faker()->unique()->randomNumber(6, true),
]);

?>
