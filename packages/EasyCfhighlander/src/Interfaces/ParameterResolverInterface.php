<?php
declare(strict_types=1);

namespace EonX\EasyCfhighlander\Interfaces;

use Symfony\Component\Console\Input\InputInterface;

interface ParameterResolverInterface
{
    public function addModifier(string $param, callable $modifier): self;

    public function addResolver(string $param, callable $resolver): self;

    public function resolve(InputInterface $input): array;

    public function setCachePathname(string $pathname): self;
}
