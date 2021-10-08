<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

interface ActorResolverInterface
{
    public function getId(): ?string;

    public function getName(): ?string;

    public function getType(): string;
}
