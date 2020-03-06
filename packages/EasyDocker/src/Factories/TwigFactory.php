<?php
declare(strict_types=1);

namespace EonX\EasyDocker\Factories;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class TwigFactory
{
    public function create(): Environment
    {
        return new Environment(new FilesystemLoader(__DIR__ . '/../../templates'));
    }
}
