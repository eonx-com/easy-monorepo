<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Laravel;

use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use Illuminate\Contracts\Translation\Translator as IlluminateTranslatorInterface;

final class Translator implements TranslatorInterface
{
    /**
     * @var \Illuminate\Contracts\Translation\Translator
     */
    private $decorated;

    public function __construct(IlluminateTranslatorInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * @param mixed[] $parameters
     */
    public function trans(string $message, array $parameters): string
    {
        // TODO: rework after upgrading all the illuminate and laravel packages to ^6.0
        $method = \method_exists($this->decorated, 'trans') ? 'trans' : 'get';

        return $this->decorated->{$method}(\trim($message), $parameters);
    }
}
