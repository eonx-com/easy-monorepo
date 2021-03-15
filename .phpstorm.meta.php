<?php
declare(strict_types=1);

namespace PHPSTORM_META;

// $container->get(Type::class) → instance of "Type"
override(\Psr\Container\ContainerInterface::get(0), type(0));

// $envelope->last(Stamp::class) -> instance of "Stamp"
override(\Symfony\Component\Messenger\Envelope::last(0), type(0));
