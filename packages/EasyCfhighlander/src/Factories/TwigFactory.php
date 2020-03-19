<?php

declare(strict_types=1);

namespace EonX\EasyCfhighlander\Factories;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class TwigFactory
{
    public function create(): Environment
    {
        $loader = new FilesystemLoader(__DIR__ . '/../../templates');

        return new Environment($loader);
    }
}
