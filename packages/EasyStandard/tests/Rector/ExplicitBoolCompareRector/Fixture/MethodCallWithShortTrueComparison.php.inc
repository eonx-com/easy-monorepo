<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\ExplicitBoolCompareRector\Fixture;

class MethodCallWithShortTrueComparison
{
    public function isValid(): bool
    {
        return true;
    }

    public function testProcess(): void
    {
        if ($this->isValid()) {
            echo 'valid';
        }
    }
}

?>
