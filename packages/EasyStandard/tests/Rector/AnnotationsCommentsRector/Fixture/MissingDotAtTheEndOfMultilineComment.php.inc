<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\AnnotationsCommentsRector\Fixture;

/**
 * Missing dot at the end
 * of multiline comment
 */
class MissingDotAtTheEndOfMultilineComment
{

}

?>
-----
<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\AnnotationsCommentsRector\Fixture;

/**
 * Missing dot at the end
 * of multiline comment.
 */
class MissingDotAtTheEndOfMultilineComment
{

}

?>