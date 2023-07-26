<?php

declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Blocks;

use EonX\EasyTemplatingBlock\Interfaces\TemplateBlockInterface;

final class TemplateBlock extends AbstractTemplatingBlock implements TemplateBlockInterface
{
    /**
     * @var mixed[]|null
     */
    private ?array $templateContext = null;

    private string $templateName;

    /**
     * @param mixed[]|null $templateContext
     */
    public static function create(
        string $name,
        string $templateName,
        ?array $templateContext = null,
    ): TemplateBlockInterface {
        return (new self($name))
            ->setTemplateName($templateName)
            ->setTemplateContext($templateContext);
    }

    /**
     * @return mixed[]|null
     */
    public function getTemplateContext(): ?array
    {
        if ($this->templateContext === null && $this->getContext() === null) {
            return null;
        }

        return \array_merge($this->getContext() ?? [], $this->templateContext ?? []);
    }

    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    /**
     * @param mixed[]|null $templateContext
     */
    public function setTemplateContext(?array $templateContext = null): self
    {
        $this->templateContext = $templateContext;

        return $this;
    }

    public function setTemplateName(string $templateName): self
    {
        $this->templateName = $templateName;

        return $this;
    }
}
