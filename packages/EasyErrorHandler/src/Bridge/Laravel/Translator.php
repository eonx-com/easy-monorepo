<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Laravel;

use EonX\EasyErrorHandler\Interfaces\TranslatorInterface;
use Illuminate\Contracts\Translation\Translator as IlluminateTranslatorInterface;
use Illuminate\Support\Str;

final class Translator implements TranslatorInterface
{
    /**
     * @var \Illuminate\Contracts\Translation\Translator
     */
    private $decorated;

    /**
     * @var string
     */
    private $translationNamespace;

    public function __construct(IlluminateTranslatorInterface $decorated, string $translationNamespace)
    {
        $this->decorated = $decorated;
        $this->translationNamespace = $translationNamespace;
    }

    /**
     * @param string $message
     * @param mixed[] $parameters
     *
     * @return string
     */
    public function trans(string $message, array $parameters): string
    {
        // TODO: rework after upgrading all the illuminate and laravel packages to ^6.0
        $method = \method_exists($this->decorated, 'trans') ? 'trans' : 'get';

        $translation = $this->decorated->{$method}(
            \implode([$this->translationNamespace, '::', \trim($message)]),
            $parameters
        );

        if ($translation !== $message && Str::startsWith($this->translationNamespace . '::', $translation)) {
            return $translation;
        }

        return $this->decorated->{$method}(\trim($message), $parameters);
    }
}
