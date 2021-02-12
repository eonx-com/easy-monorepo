<?php

declare(strict_types=1);

namespace EonX\EasyStandard\Rector;

use Nette\Utils\Strings;
use PhpParser\Comment;
use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \EonX\EasyStandard\Tests\Rector\SingleLineCommentRector\SingleLineCommentRectorTest
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
        return [Node::class];
    }

    public function refactor(Node $node): ?Node
    {
        $comments = $node->getComments();

        if (\count($comments) !== 0) {
            $comments = $this->checkComments($comments);
            $node->setAttribute('comments', $comments);
        }

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
            $oldCommentText = $comment->getText();
            if (Strings::startsWith($oldCommentText, '/**')) {
                $newComments[] = $comment;
                continue;
            }

            $commentText = Strings::firstUpper(Strings::trim(Strings::replace($oldCommentText, '#\/\/#', '')));

            if ($this->isLineEndingWithDisallowed($commentText)) {
                $commentText = Strings::substring($commentText, 0, -1);
            }

            $commentText = '// ' . $commentText;

            if ($oldCommentText !== $commentText) {
                $comment = new Comment(
                    $commentText,
                    $comment->getStartLine(),
                    $comment->getStartFilePos(),
                    $comment->getStartTokenPos(),
                    $comment->getEndLine(),
                    $comment->getEndFilePos(),
                    $comment->getEndTokenPos()
                );
            }

            $newComments[] = $comment;
        }

        return $newComments;
    }

    private function isLineEndingWithDisallowed(string $docLineContent): bool
    {
        $lastCharacter = Strings::substring($docLineContent, -1);

        return \in_array($lastCharacter, $this->disallowedEnd, true);
    }
}
