<?php

declare(strict_types=1);

namespace EonX\EasyTest\InvalidDataMaker;

use LogicException;
use RuntimeException;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @codeCoverageIgnore
 */
abstract class AbstractInvalidDataMaker
{
    /**
     * @var string
     */
    private const PLURAL_PARAM = '%count%';

    /**
     * @var string[]
     */
    protected static array $translations = ['vendor/symfony/validator/Resources/translations/validators.en.xlf'];

    protected ?string $relatedProperty = null;

    protected ?string $relatedPropertyValue = null;

    private static ?TranslatorInterface $translator = null;

    private bool $asArrayElement = false;

    private bool $asString = false;

    private ?string $message = null;

    private ?string $propertyPath = null;

    private ?string $wrapWith = null;

    final public function __construct(
        protected string $property,
    ) {
        self::initTranslator();
    }

    final public static function addTranslations(string $translations): void
    {
        self::$translations[] = $translations;
    }

    public static function make(string $property): static
    {
        return new static($property);
    }

    /**
     * @param mixed $value
     *
     * @return mixed[]
     */
    final protected function create(string $caseName, $value, ?string $message = null): array
    {
        if ($this->asString === true) {
            $value = (string)$value;
        }

        if ($this->asArrayElement === true) {
            $value = [$value];
        }

        $invalidData = [$this->property => $value];

        if ($this->relatedProperty !== null && $this->relatedPropertyValue !== null) {
            $invalidData[$this->relatedProperty] = $this->relatedPropertyValue;
        }

        $propertyPath = $this->resolvePropertyPath($invalidData);

        if ($this->wrapWith !== null) {
            $propertyPath = $this->wrapWith . '.' . $propertyPath;
            $caseName = \str_replace($this->property, $propertyPath, $caseName);
            $invalidData = [$this->wrapWith => $invalidData];
        }

        return [
            $caseName => [
                'data' => $invalidData,
                'propertyPath' => $this->resolvePropertyPath($invalidData),
                'validationErrorMessage' => (string)($this->message ?? $message),
            ],
        ];
    }

    /**
     * @param mixed[]|null $params
     */
    final protected function translateMessage(string $messageKey, ?array $params = null, ?int $plural = null): string
    {
        $params[self::PLURAL_PARAM] = $plural;

        if (self::$translator === null) {
            throw new RuntimeException('Translator not initialized.');
        }

        return self::$translator->trans($messageKey, $params);
    }

    private static function createTranslationLoader(string $extension): LoaderInterface
    {
        if (\in_array($extension, ['yaml', 'yml'], true)) {
            return new YamlFileLoader();
        }

        if ($extension === 'xlf') {
            return new XliffFileLoader();
        }

        throw new LogicException('Only YAML and XLF translation formats are supported.');
    }

    private static function initTranslator(): void
    {
        if (self::$translator !== null) {
            return;
        }

        $locale = 'en';
        $translator = new Translator($locale);

        foreach (self::$translations as $translation) {
            $extension = \strtolower(\pathinfo($translation, \PATHINFO_EXTENSION));
            $translator->addLoader($extension, self::createTranslationLoader($extension));
            $translator->addResource($extension, $translation, $locale);
        }

        self::$translator = $translator;
    }

    /**
     * @param mixed[] $invalidData
     *
     * @noinspection MultipleReturnStatementsInspection
     */
    private function resolvePropertyPath(array $invalidData): string
    {
        if ($this->propertyPath !== null) {
            return $this->propertyPath;
        }

        $propertyName = (string)\array_key_first($invalidData);

        if (\is_array($invalidData[$propertyName]) && \count($invalidData[$propertyName]) > 0) {
            // The case of stubs collection ('prop' => [ [], [], [], [] ])
            if (($invalidData[$propertyName][0] ?? null) === []) {
                return $propertyName;
            }

            $currentProperty = \current(\array_keys($invalidData[$propertyName]));

            if ($currentProperty === 0) {
                return $propertyName . '[0]';
            }

            return $propertyName . '.' . $this->resolvePropertyPath($invalidData[$propertyName]);
        }

        return $propertyName;
    }

    /**
     * @return static
     */
    final public function asArrayElement(): self
    {
        $this->asArrayElement = true;

        return $this;
    }

    /**
     * @return static
     */
    final public function asString(): self
    {
        $this->asString = true;

        return $this;
    }

    /**
     * @return static
     */
    final public function message(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return static
     */
    final public function propertyPath(string $propertyPath): self
    {
        $this->propertyPath = $propertyPath;

        return $this;
    }

    /**
     * @return static
     */
    final public function wrapWith(string $wrapWith): self
    {
        $this->wrapWith = $wrapWith;

        return $this;
    }
}
