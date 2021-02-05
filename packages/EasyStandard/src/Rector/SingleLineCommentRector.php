<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Rector;

use Nette\Utils\Strings;
use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \EonX\EasyStandard\Tests\Rector\PhpDocCommentRector\PhpDocCommentRectorTest
 */
final class SingleLineCommentRector extends AbstractRector
{
    /**
     * @var string[]
     */
    public $disallowedEnd = ['.', ',', '?', ':', '!'];

    /**
     * From this method documentation is generated.
     */
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Corrects single line comment',
            [
                new CodeSample(
                    <<<'PHP'
// some class.
class SomeClass
{
}
PHP
                    ,
                    <<<'PHP'
// Some class
class SomeClass
{
}
PHP
                ),
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->walkNodeRecursive($node);

        return $node;
    }

    /**
     * @param \PhpParser\Comment[] $comments
     *
     * @return \PhpParser\Comment[]
     */
    private function checkComments(array $comments): array
    {
        $newComments = [];

        foreach ($comments as $comment) {
            $commentText = $comment->getText();
            if (Strings::startsWith($commentText, '/**')) {
                $newComments[] = $comment;
                continue;
            }

            $commentText = Strings::firstUpper(Strings::trim(Strings::replace($commentText, '#\/\/#', '')));

            if ($this->isLineEndingWithDisallowed($commentText)) {
                $commentText = Strings::substring($commentText, 0, -1);
            }

            $newComments[] = new Comment(
                '// ' . $commentText,
                $comment->getStartLine(),
                $comment->getStartFilePos(),
                $comment->getStartTokenPos(),
                $comment->getEndLine(),
                $comment->getEndFilePos(),
                $comment->getEndTokenPos()
            );
        }

        return $newComments;
    }

    private function isLineEndingWithDisallowed(string $docLineContent): bool
    {
        $lastCharacter = Strings::substring($docLineContent, -1);

        return \in_array($lastCharacter, $this->disallowedEnd, true);
    }

    private function walkNodeRecursive(Node $node): void
    {
        $comments = $node->getComments();

        if (\count($comments) !== 0) {
            $comments = $this->checkComments($comments);
            $node->setAttribute('comments', $comments);
        }

        /** @var ClassLike $node */
        if (\in_array('stmts', $node->getSubNodeNames(), true) && $node->stmts !== null) {
            foreach ($node->stmts as $stmt) {
                $this->walkNodeRecursive($stmt);
            }
        }

        /** @var Foreach_ $node */
        if (\in_array('expr', $node->getSubNodeNames(), true) && $node->expr !== null) {
            $this->walkNodeRecursive($node->expr);
        }

        /** @var FuncCall $node */
        if ($node instanceof FuncCall) {
            foreach ($node->args as $arg) {
                $this->walkNodeRecursive($arg->value);
            }
        }

        /** @var Array_ $node */
        if ($node instanceof Array_ && $node->items !== null) {
            /** @var ArrayItem|null $item */
            foreach ($node->items as $item) {
                if ($item === null) {
                    continue;
                }

                if ($item->key !== null) {
                    $this->walkNodeRecursive($item->key);
                }

                $this->walkNodeRecursive($item->value);
            }
        }
    }
}
