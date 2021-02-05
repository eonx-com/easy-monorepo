<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\SingleLineCommentRector\Fixture;

/**
 * Class ClassHasCoversTest
 * new class
 * for work.
 *
 * Another comment.
 *
 * @ApiResource(
 *     collectionOperations={
 *          "post"={
 *              "denormalization_context"={"groups"={"business:create"}},
 *              "security"="is_granted(permission('PERMISSION_BUSINESS_CREATE'))",
 *              "validation_groups"={"Default", "createValidation"},
 *          },
 *     },
 *     itemOperations={
 *          "get"={
 *              "security"="is_granted(permission('PERMISSION_BUSINESS_READ'))"
 *          },
 *          "put"={
 *              "denormalization_context"={"groups"={"business:update"}},
 *              "security"="is_granted(permission('PERMISSION_BUSINESS_UPDATE'))"
 *          }
 *     },
 *     normalizationContext={"groups"={"business:read", "primary_key:read", "timestamp:read"}},
 * )
 *
 * @covers SomeClass some comment
 */
class ClassHasCorrectSingleLineComments
{
    /**
     * My const.
     *
     * @var string
     */
    private const TEST_CONST = 'const';

    /**
     * My property.
     *
     * @Assert\NotNull() some description
     *
     * @see Another code
     *
     * @var string
     */
    private $test;

    /** Single line PHPDoc. */
    private $trait;

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
    public function setTest2(bool $test2): self
    {
        // Some comment
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
            } // First end

        }, [$test2]);

        return $this;
    }
}
