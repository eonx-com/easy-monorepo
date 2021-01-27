<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\PhpDocCommentRector\Fixture;

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
class ClassHasCorrectPHPDoсTest
{
    /** Single line PHPDoc. */
    private $trait;

    /**
     * My const.
     *
     * @var string
     */
    private const TEST_CONST = 'const';

    /**
     * My property.
     *
     * @Assert\NotNull()
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
    public function setTest2(bool $test2): self
    {
        $this->test = (bool)\array_map(function ($elem) {
            $var = [
                '1',
                '2' => [
                    '3' => function () use ($elem) {
                        return $elem !== true;
                    },
                    '4' => $this->getTest(),
                ],
            ];
            foreach ($var as $key => $value) {
                /**
                 * Some text.
                 *
                 * @var mixed[]|string $value may be refactor
                 */
                if (\is_array($value)) {
                    foreach ($value as $key2 => $value2) {
                        continue;
                    }

                    /**
                     * Another text 1.
                     *
                     * Another text 2.
                     *
                     * @var string[] $test3 some text
                     */
                    $test3['123'] = '123';
                }
            }
        }, [$test2]);

        return $this;
    }
}
