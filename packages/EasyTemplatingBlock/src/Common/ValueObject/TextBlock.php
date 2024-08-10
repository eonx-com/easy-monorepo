<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Common\ValueObject;

final class TextBlock extends AbstractTemplatingBlock
{
    private string $contents;

    public static function create(string $name, string $contents): self
    {
        return (new self($name))->setContents($contents);
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    public function setContents(string $contents): self
    {
        $this->contents = $contents;

        return $this;
    }
}
