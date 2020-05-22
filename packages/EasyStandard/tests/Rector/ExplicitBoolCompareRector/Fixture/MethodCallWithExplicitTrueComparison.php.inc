<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\ExplicitBoolCompareRector\Fixture;

class MethodCallWithExplicitTrueComparison
{
    public function isValid(): bool
    {
        return true;
    }

    public function testProcess(): void
    {
        if ($this->isValid() === true) {
            echo 'valid';
        }
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\ExplicitBoolCompareRector\Fixture;

class MethodCallWithExplicitTrueComparison
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
