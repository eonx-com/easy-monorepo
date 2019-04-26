<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyCfhighlander\Factories;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class TwigFactory
{
    /**
     * Create twig.
     *
     * @return \Twig\Environment
     */
    public function create(): Environment
    {
        $loader = new FilesystemLoader(__DIR__ . '/../../templates');

        return new Environment($loader);
    }
}
