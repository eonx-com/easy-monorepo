<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Helpers;

use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

final class VarDumpHelper
{
    /**
     * @var callable|null
     */
    private static $dumper;

    public static function dump(mixed $var): string
    {
        if (self::$dumper === null) {
            self::setDumper();
        }

        return (self::$dumper)($var);
    }

    private static function setDumper(): void
    {
        if (\class_exists(VarCloner::class) && \class_exists(HtmlDumper::class)) {
            $cloner = new VarCloner();
            $cloner->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO);

            $dumper = new HtmlDumper();

            self::$dumper = static function (mixed $var) use ($cloner, $dumper): ?string {
                return $dumper->dump($cloner->cloneVar($var), true);
            };

            return;
        }

        // Fallback if symfony/var-dumper not installed
        self::$dumper = static function (mixed $var): string {
            return \print_r($var, true);
        };
    }
}
