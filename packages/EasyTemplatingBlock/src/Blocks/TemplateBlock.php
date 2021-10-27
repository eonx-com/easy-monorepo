<?php

declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Blocks;

use EonX\EasyTemplatingBlock\Interfaces\TemplateBlockInterface;

final class TemplateBlock extends AbstractTemplatingBlock implements TemplateBlockInterface
{
    /**
     * @var null|mixed[]
     */
    private $templateContext;

    /**
     * @var string
     */
    private $templateName;

    /**
     * @return null|mixed[]
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
     * @param null|mixed[] $templateContext
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
