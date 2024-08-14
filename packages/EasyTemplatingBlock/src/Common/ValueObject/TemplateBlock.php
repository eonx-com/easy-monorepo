<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Common\ValueObject;

final class TemplateBlock extends AbstractTemplatingBlock
{
    private ?array $templateContext = null;

    private string $templateName;

    public static function create(
        string $name,
        string $templateName,
        ?array $templateContext = null,
    ): self {
        return (new self($name))
            ->setTemplateName($templateName)
            ->setTemplateContext($templateContext);
    }

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
