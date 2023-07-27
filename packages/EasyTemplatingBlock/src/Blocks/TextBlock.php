<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Blocks;

use EonX\EasyTemplatingBlock\Interfaces\TextBlockInterface;

final class TextBlock extends AbstractTemplatingBlock implements TextBlockInterface
{
    private string $contents;

    public static function create(string $name, string $contents): TextBlockInterface
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
