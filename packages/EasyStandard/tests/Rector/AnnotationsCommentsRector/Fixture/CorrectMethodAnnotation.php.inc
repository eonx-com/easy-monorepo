<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\AnnotationsCommentsRector\Fixture;

class CorrectMethodAnnotation
{
    /**
     * @inheritdoc
     */
    public function someMethod()
    {
        // no body
    }
}
