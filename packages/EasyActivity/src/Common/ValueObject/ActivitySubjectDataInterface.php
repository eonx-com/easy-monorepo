<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\ValueObject;

interface ActivitySubjectDataInterface
{
    public function getSubjectData(): ?string;

    public function getSubjectOldData(): ?string;
}
