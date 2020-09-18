<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\AnnotationsCommentsRector\Fixture;

/**
 * Missing dot at the end of comment
 */
class MissingDotAtTheEndOfComment
{

}

?>
-----
<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\AnnotationsCommentsRector\Fixture;

/**
 * Missing dot at the end of comment.
 */
class MissingDotAtTheEndOfComment
{

}

?>