<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\AnnotationsCommentsRector\Fixture;

class SkipIgnoreDoc
{
    /**
     * @{inheritDoc}
     */
    public function someMethod()
    {
    }
}
