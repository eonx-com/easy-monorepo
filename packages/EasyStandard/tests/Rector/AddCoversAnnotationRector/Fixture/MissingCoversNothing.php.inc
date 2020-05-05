<?php

namespace EonX\EasyStandard\Tests\Rector\AddCoversAnnotationRector\Fixture;

use PHPUnit\Framework\TestCase;

class SomethingTest extends TestCase
{

}
?>
-----
<?php

namespace EonX\EasyStandard\Tests\Rector\AddCoversAnnotationRector\Fixture;

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class SomethingTest extends TestCase
{

}
?>