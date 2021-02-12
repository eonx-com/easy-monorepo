<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\PhpDocCommentRector\Fixture;

/**
 * class SomeClass
 *   new class
 *      for work
 *
 *   another comment
 */
class ClassHasInCorrectPhpDoсTest
{
    /**
     * My super const
     *
     * @var string
     */
    private const TET_CONST = 'const';

    /**
     * my property comment
     *
     * @Assert\NotNull()        Some description.
     *
     * @see Another code
     *
     * @var string
     */
    private $test;

    /**
     * My function comment
     *
     * @return string
     */
    public function getTest(): string
    {
        return $this->test;
    }

    /**
     * setter
     *
     * @param bool $test2
     *
     * @return static
     */
    public function setTest(bool $test2): self
    {
        $this->test = (bool)\array_map(function ($elem) {
            $var = [
                '1',
                '2' => [
                    '4' => $this->getTest(),
                ],
            ];
            foreach ($var as $key => $value) { // 1-st start
                /**
                 * Some text
                 *
                 * @var mixed[]|string $value       May be refactor?
                 */
                if (\is_array($value)) {
                    // Try to loop
                    foreach ($value as $key2 => $value2) { // 2-nd start
                        // Do nothing
                        continue;
                    } // 2-nd end
                    /**
                     * another text.
                     *
                     * @var string[] $test3 some text.
                     */
                    $test3['123'] = '123';
                }
            } // first end
        }, [$test2]);

        return $this;
    }
}
?>

-----
<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\PhpDocCommentRector\Fixture;

/**
 * Class SomeClass
 * new class
 * for work.
 *
 *   Another comment.
 */
class ClassHasInCorrectPhpDoсTest
{
    /**
     * My super const.
     *
     * @var string
     */
    private const TET_CONST = 'const';

    /**
     * My property comment.
     *
     * @Assert\NotNull() some description
     *
     * @see Another code
     *
     * @var string
     */
    private $test;

    /**
     * My function comment.
     *
     * @return string
     */
    public function getTest(): string
    {
        return $this->test;
    }

    /**
     * Setter.
     *
     * @param bool $test2
     *
     * @return static
     */
    public function setTest(bool $test2): self
    {
        $this->test = (bool)\array_map(function ($elem) {
            $var = [
                '1',
                '2' => [
                    '4' => $this->getTest(),
                ],
            ];
            foreach ($var as $key => $value) { // 1-st start
                /**
                 * Some text.
                 *
                 * @var mixed[]|string $value       may be refactor
                 */
                if (\is_array($value)) {
                    // Try to loop
                    foreach ($value as $key2 => $value2) { // 2-nd start
                        // Do nothing
                        continue;
                    } // 2-nd end
                    /**
                     * Another text.
                     *
                     * @var string[] $test3 some text
                     */
                    $test3['123'] = '123';
                }
            } // first end
        }, [$test2]);

        return $this;
    }
}
?>
