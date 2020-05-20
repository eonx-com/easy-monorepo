<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\ExplicitBoolCompareRector\Fixture;

class MethodCallWithNegate
{
    public function isValid(): bool
    {
        return true;
    }

    public function testProcess(): void
    {
        if (!$this->isValid()) {
            echo 'not valid';
        }
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\ExplicitBoolCompareRector\Fixture;

class MethodCallWithNegate
{
    public function isValid(): bool
    {
        return true;
    }

    public function testProcess(): void
    {
        if ($this->isValid() === false) {
            echo 'not valid';
        }
    }
}

?>
