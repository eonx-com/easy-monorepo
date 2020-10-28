<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\CorrectInheritDocAnnotation\Fixture;

use PHPUnit\Framework\TestCase;

class IncorrectInheritDocAnnotation extends TestCase
{
    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function someMethod(): int
    {
        return 1;
    }
}

?>
-----
<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\CorrectInheritDocAnnotation\Fixture;

use PHPUnit\Framework\TestCase;

class IncorrectInheritDocAnnotation extends TestCase
{
    /**
     * {@inheritDoc}
     *
     * @return int
     */
    public function someMethod(): int
    {
        return 1;
    }
}

?>