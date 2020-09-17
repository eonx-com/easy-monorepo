<?php

declare(strict_types=1);

/**
 * Checks function comment blocks follow our standards.
 *
 * @author Nathan Page <nathan.page@loyaltycorp.com.au>
 * @copyright 2018 Loyalty Corp Pty Ltd (ABN 39 615 958 873)
 * @license https://github.com/loyaltycorp/standards/blob/master/licence BSD Licence
 */

namespace EonX\EasyStandard\Sniffs\Commenting;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\Commenting\FunctionCommentSniff as SquizFunctionCommentSniff;
use PHP_CodeSniffer\Util\Common;
use PHP_CodeSniffer\Util\Tokens;
use SlevomatCodingStandard\Helpers\SuppressHelper;

class FunctionCommentSniff extends SquizFunctionCommentSniff
{
    /**
     * Cache for class parents and interfaces.
     *
     * @var mixed[]
     */
    private $parentsAndInterfaces;

    /**
     * @var \PHP_CodeSniffer\Files\File
     */
    private $phpcsFile;

    /**
     * {@inheritdoc}
     *
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException
     * @throws \PHP_CodeSniffer\Exceptions\TokenizerException
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $this->phpcsFile = $phpcsFile;

        $tokens = $phpcsFile->getTokens();
        $find = Tokens::$methodPrefixes;
        $find[] = T_WHITESPACE;

        $commentEnd = $phpcsFile->findPrevious($find, $stackPtr - 1, null, true);
        if ($tokens[$commentEnd]['code'] === T_COMMENT) {
            // Inline comments might just be closing comments for
            // control structures or functions instead of function comments
            // using the wrong comment type. If there is other code on the line,
            // assume they relate to that code.
            $prev = $phpcsFile->findPrevious($find, $commentEnd - 1, null, true);
            if ($prev !== false && $tokens[$prev]['line'] === $tokens[$commentEnd]['line']) {
                $commentEnd = $prev;
            }
        }

        if ($tokens[$commentEnd]['code'] !== T_DOC_COMMENT_CLOSE_TAG && $tokens[$commentEnd]['code'] !== T_COMMENT) {
            $phpcsFile->addError('Missing function doc comment', $stackPtr, 'Missing');
            $phpcsFile->recordMetric($stackPtr, 'Function has doc comment', 'no');

            return;
        }

        $phpcsFile->recordMetric($stackPtr, 'Function has doc comment', 'yes');

        if ($tokens[$commentEnd]['code'] === T_COMMENT) {
            $phpcsFile->addError('You must use "/**" style comments for a function comment', $stackPtr, 'WrongStyle');

            return;
        }

        if ($tokens[$commentEnd]['line'] !== $tokens[$stackPtr]['line'] - 1) {
            $error = 'There must be no blank lines after the function comment';
            $phpcsFile->addError($error, $commentEnd, 'SpacingAfter');
        }

        $commentStart = $tokens[$commentEnd]['comment_opener'];
        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
            if ($tokens[$tag]['content'] === '@see') {
                // Make sure the tag isn't empty.
                $string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);
                if ($string === false || $tokens[$string]['line'] !== $tokens[$tag]['line']) {
                    $error = 'Content missing for @see tag in function comment';
                    $phpcsFile->addError($error, $tag, 'EmptySees');
                }
            }
        }

        // [2] Added {@inheritdoc} validation for override methods.
        if ($this->validateInheritdoc($phpcsFile, $stackPtr, $commentStart, $commentEnd)) {
            return;
        }

        parent::process($phpcsFile, $stackPtr);
    }

    /**
     * Process function parameters.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $stackPtr
     * @param int $commentStart
     *
     * @throws \PHP_CodeSniffer\Exceptions\DeepExitException
     * @throws \PHP_CodeSniffer\Exceptions\TokenizerException
     */
    protected function processParams(File $phpcsFile, $stackPtr, $commentStart): void
    {
        $phpVersion = Config::getConfigData('php_version') ?? PHP_VERSION_ID;

        // Improve suggested types to include short versions
        Common::$allowedTypes = \array_merge(Common::$allowedTypes, ['int', 'bool']);

        $tokens = $phpcsFile->getTokens();

        $params = [];
        $maxType = 0;
        $maxVar = 0;
        foreach ($tokens[$commentStart]['comment_tags'] as $pos => $tag) {
            if ($tokens[$tag]['content'] !== '@param') {
                continue;
            }

            $type = '';
            $typeSpace = 0;
            $var = '';
            $varSpace = 0;
            $comment = '';
            $commentLines = [];
            if ($tokens[$tag + 2]['code'] === T_DOC_COMMENT_STRING) {
                $matches = [];
                \preg_match(
                    '/([^$&.]+)(?:((?:\.\.\.)?(?:\$|&)[^\s]+)(?:(\s+)(.*))?)?/',
                    $tokens[$tag + 2]['content'],
                    $matches
                );

                if (empty($matches) === false) {
                    $typeLen = \strlen($matches[1]);
                    $type = \trim($matches[1]);
                    $typeSpace = $typeLen - \strlen($type);
                    $typeLen = \strlen($type);
                    if ($typeLen > $maxType) {
                        $maxType = $typeLen;
                    }
                }

                if (isset($matches[2]) === true) {
                    $var = $matches[2];
                    $varLen = \strlen($var);
                    if ($varLen > $maxVar) {
                        $maxVar = $varLen;
                    }

                    if (isset($matches[4]) === true) {
                        $varSpace = \strlen($matches[3]);
                        $comment = $matches[4];
                        $commentLines[] = [
                            'comment' => $comment,
                            'token' => $tag + 2,
                            'indent' => $varSpace,
                        ];

                        // Any strings until the next tag belong to this comment.
                        if (isset($tokens[$commentStart]['comment_tags'][$pos + 1]) === true) {
                            $end = $tokens[$commentStart]['comment_tags'][$pos + 1];
                        } else {
                            $end = $tokens[$commentStart]['comment_closer'];
                        }

                        for ($i = $tag + 3; $i < $end; $i++) {
                            if ($tokens[$i]['code'] === T_DOC_COMMENT_STRING) {
                                $indent = 0;
                                if ($tokens[$i - 1]['code'] === T_DOC_COMMENT_WHITESPACE) {
                                    $indent = \strlen($tokens[$i - 1]['content']);
                                }

                                $comment .= ' ' . $tokens[$i]['content'];
                                $commentLines[] = [
                                    'comment' => $tokens[$i]['content'],
                                    'token' => $i,
                                    'indent' => $indent,
                                ];
                            }
                        }
                    } else {
                        // Ignoring parameter comment for now
                        // $error = 'Missing parameter comment';
                        // $phpcsFile->addError($error, $tag, 'MissingParamComment');
                        $commentLines[] = ['comment' => ''];
                    }//end if
                } else {
                    $error = 'Missing parameter name';
                    $phpcsFile->addError($error, $tag, 'MissingParamName');
                }//end if
            } else {
                $error = 'Missing parameter type';
                $phpcsFile->addError($error, $tag, 'MissingParamType');
            }//end if

            $params[] = [
                'tag' => $tag,
                'type' => $type,
                'var' => $var,
                'comment' => $comment,
                'commentLines' => $commentLines,
                'type_space' => $typeSpace,
                'var_space' => $varSpace,
            ];
        }//end foreach

        $realParams = $phpcsFile->getMethodParameters($stackPtr);
        $foundParams = [];

        // We want to use ... for all variable length arguments, so added
        // this prefix to the variable name so comparisons are easier.
        foreach ($realParams as $pos => $param) {
            if ($param['variable_length'] === true) {
                $realParams[$pos]['name'] = '...' . $realParams[$pos]['name'];
            }
        }

        foreach ($params as $pos => $param) {
            // If the type is empty, the whole line is empty.
            if ($param['type'] === '') {
                continue;
            }

            // Check the param type value.
            $typeNames = \explode('|', $param['type']);
            $suggestedTypeNames = [];

            foreach ($typeNames as $typeName) {
                // Strip nullable operator.
                if ($typeName[0] === '?') {
                    $typeName = \substr($typeName, 1);
                }

                $suggestedName = Common::suggestType($typeName);
                $suggestedTypeNames[] = $suggestedName;

                if (\count($typeNames) > 1) {
                    continue;
                }

                // Check type hint for array and custom type.
                $suggestedTypeHint = '';
                if (\strpos($suggestedName, 'array') !== false || \substr($suggestedName, -2) === '[]') {
                    $suggestedTypeHint = 'array';
                } else {
                    if (\strpos($suggestedName, 'callable') !== false) {
                        $suggestedTypeHint = 'callable';
                    } else {
                        if (\strpos($suggestedName, 'callback') !== false) {
                            $suggestedTypeHint = 'callable';
                        } else {
                            if (\in_array($suggestedName, Common::$allowedTypes, true) === false) {
                                $suggestedTypeHint = $suggestedName;
                            }
                        }
                    }
                }

                if ($phpVersion >= 70000) {
                    if ($suggestedName === 'string') {
                        $suggestedTypeHint = 'string';
                    } else {
                        if ($suggestedName === 'int' || $suggestedName === 'integer') {
                            $suggestedTypeHint = 'int';
                        } else {
                            if ($suggestedName === 'float') {
                                $suggestedTypeHint = 'float';
                            } else {
                                if ($suggestedName === 'bool' || $suggestedName === 'boolean') {
                                    $suggestedTypeHint = 'bool';
                                }
                            }
                        }
                    }
                }

                if ($phpVersion >= 70200 && $suggestedName === 'object') {
                    $suggestedTypeHint = 'object';
                }

                if ($suggestedTypeHint !== '' && isset($realParams[$pos]) === true) {
                    $typeHint = $realParams[$pos]['type_hint'];

                    // Remove namespace prefixes when comparing.
                    $compareTypeHint = \substr($suggestedTypeHint, \strlen($typeHint) * -1);

                    if ($typeHint === '') {
                        $error = 'Type hint "%s" missing for %s';
                        $data = [
                            $suggestedTypeHint,
                            $param['var'],
                        ];

                        $errorCode = 'TypeHintMissing';
                        if ($suggestedTypeHint === 'string'
                            || $suggestedTypeHint === 'int'
                            || $suggestedTypeHint === 'float'
                            || $suggestedTypeHint === 'bool'
                        ) {
                            $errorCode = 'Scalar' . $errorCode;
                        }

                        $suppressName = 'EoneoPay.Commenting.FunctionComment.' . $errorCode;
                        if (SuppressHelper::isSniffSuppressed($phpcsFile, $stackPtr, $suppressName) === false) {
                            $phpcsFile->addError($error, $stackPtr, $errorCode, $data);
                        }
                    } else {
                        if ($typeHint !== $compareTypeHint
                            && $typeHint !== '?' . $compareTypeHint
                            && \in_array($typeHint, Common::$allowedTypes, true)) {
                            // Perform this check only if type hint is not an object, really hard to validate with aliases
                            $error = 'Expected type hint "%s"; found "%s" for %s';
                            $data = [
                                $suggestedTypeHint,
                                $typeHint,
                                $param['var'],
                            ];
                            $phpcsFile->addError($error, $stackPtr, 'IncorrectTypeHint', $data);
                        }
                    }//end if
                } else {
                    if ($suggestedTypeHint === '') {
                        $typeHint = $realParams[$pos]['type_hint'];
                        if ($typeHint !== '') {
                            $error = 'Unknown type hint "%s" found for %s';
                            $data = [
                                $typeHint,
                                $param['var'],
                            ];
                            $phpcsFile->addError($error, $stackPtr, 'InvalidTypeHint', $data);
                        }
                    }
                }//end if
            }//end foreach

            $suggestedType = \implode('|', $suggestedTypeNames);
            if ($param['type'] !== $suggestedType) {
                $error = 'Expected "%s" but found "%s" for parameter type';
                $data = [
                    $suggestedType,
                    $param['type'],
                ];

                $fix = $phpcsFile->addFixableError($error, $param['tag'], 'IncorrectParamVarName', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();

                    $content = $suggestedType;
                    $content .= \str_repeat(' ', $param['type_space']);
                    $content .= $param['var'];
                    $content .= \str_repeat(' ', $param['var_space']);
                    if (isset($param['commentLines'][0]) === true) {
                        $content .= $param['commentLines'][0]['comment'];
                    }

                    $phpcsFile->fixer->replaceToken($param['tag'] + 2, $content);

                    // Fix up the indent of additional comment lines.
                    foreach ($param['commentLines'] as $lineNum => $line) {
                        if ($lineNum === 0
                            || $param['commentLines'][$lineNum]['indent'] === 0
                        ) {
                            continue;
                        }

                        $diff = \strlen($param['type']) - \strlen($suggestedType);
                        $newIndent = $param['commentLines'][$lineNum]['indent'] - $diff;
                        $phpcsFile->fixer->replaceToken(
                            $param['commentLines'][$lineNum]['token'] - 1,
                            str_repeat(' ', $newIndent)
                        );
                    }

                    $phpcsFile->fixer->endChangeset();
                }//end if
            }//end if

            if ($param['var'] === '') {
                continue;
            }

            $foundParams[] = $param['var'];

            // Check number of spaces after the type.
            //$this->checkSpacingAfterParamType($phpcsFile, $param, $maxType);

            // Make sure the param name is correct.
            if (isset($realParams[$pos]) === true) {
                $realName = $realParams[$pos]['name'];
                if ($realName !== $param['var']) {
                    $code = 'ParamNameNoMatch';
                    $data = [
                        $param['var'],
                        $realName,
                    ];

                    $error = 'Doc comment for parameter %s does not match ';
                    if (\strtolower($param['var']) === \strtolower($realName)) {
                        $error .= 'case of ';
                        $code = 'ParamNameNoCaseMatch';
                    }

                    $error .= 'actual variable name %s';

                    $phpcsFile->addError($error, $param['tag'], $code, $data);
                }
            } else {
                if (\substr($param['var'], -4) !== ',...') {
                    // We must have an extra parameter comment.
                    $error = 'Superfluous parameter comment';
                    $phpcsFile->addError($error, $param['tag'], 'ExtraParamComment');
                }
            }//end if

            if ($param['comment'] === '') {
                continue;
            }

            // Check number of spaces after the var name.
            //$this->checkSpacingAfterParamName($phpcsFile, $param, $maxVar);

            // Param comments must start with a capital letter and end with the full stop.
            if (\preg_match('/^(?=\p{Ll}|\P{L})(?=\D)/u', $param['comment']) === 1) {
                $error = 'Parameter comment must start with a capital letter';
                $phpcsFile->addError($error, $param['tag'], 'ParamCommentNotCapital');
            }

            // Don't enforce the full stop for now
//            $lastChar = substr($param['comment'], -1);
//            if ($lastChar !== '.') {
//                $error = 'Parameter comment must end with a full stop';
//                $phpcsFile->addError($error, $param['tag'], 'ParamCommentFullStop');
//            }
        }//end foreach

        $realNames = [];
        foreach ($realParams as $realParam) {
            $realNames[] = $realParam['name'];
        }

        // Report missing comments.
        $diff = \array_diff($realNames, $foundParams);
        foreach ($diff as $neededParam) {
            $error = 'Doc comment for parameter "%s" missing';
            $data = [$neededParam];
            $phpcsFile->addError($error, $commentStart, 'MissingParamTag', $data);
        }
    }

    /**
     * Process the return comment of this function comment.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int $stackPtr The position of the current token in the stack passed in $tokens.
     * @param int $commentStart The position in the stack where the comment started.
     *
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException
     */
    protected function processReturn(File $phpcsFile, $stackPtr, $commentStart): void
    {
        $tokens = $phpcsFile->getTokens();

        // Skip constructor and destructor.
        $methodName = $phpcsFile->getDeclarationName($stackPtr);
        $isSpecialMethod = ($methodName === '__construct' || $methodName === '__destruct');

        $return = null;
        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
            if ($tokens[$tag]['content'] === '@return') {
                if ($return !== null) {
                    $error = 'Only 1 @return tag is allowed in a function comment';
                    $phpcsFile->addError($error, $tag, 'DuplicateReturn');

                    return;
                }

                $return = $tag;
            }
        }

        if ($isSpecialMethod === true) {
            return;
        }

        if ($return !== null) {
            $content = $tokens[$return + 2]['content'];
            if (empty($content) === true || $tokens[$return + 2]['code'] !== T_DOC_COMMENT_STRING) {
                $error = 'Return type missing for @return tag in function comment';
                $phpcsFile->addError($error, $return, 'MissingReturnType');
            } else {
                // Support both a return type and a description.
                \preg_match('`^((?:\|?(?:array\([^\)]*\)|[\\\\a-z0-9\[\]]+))*)( .*)?`i', $content, $returnParts);
                if (isset($returnParts[1]) === false) {
                    return;
                }

                $returnType = $returnParts[1];
                $typeNames = \explode('|', $returnType);

                // If the return type is void, make sure there is
                // no return statement in the function.
                if ($returnType === 'void') {
                    if (isset($tokens[$stackPtr]['scope_closer']) === true) {
                        $endToken = $tokens[$stackPtr]['scope_closer'];
                        for ($returnToken = $stackPtr; $returnToken < $endToken; $returnToken++) {
                            if ($tokens[$returnToken]['code'] === T_CLOSURE
                                || $tokens[$returnToken]['code'] === T_ANON_CLASS
                            ) {
                                $returnToken = $tokens[$returnToken]['scope_closer'];
                                continue;
                            }

                            if ($tokens[$returnToken]['code'] === T_RETURN
                                || $tokens[$returnToken]['code'] === T_YIELD
                                || $tokens[$returnToken]['code'] === T_YIELD_FROM
                            ) {
                                break;
                            }
                        }

                        if ($returnToken !== $endToken) {
                            // If the function is not returning anything, just
                            // exiting, then there is no problem.
                            $semicolon = $phpcsFile->findNext(T_WHITESPACE, $returnToken + 1, null, true);
                            if ($tokens[$semicolon]['code'] !== T_SEMICOLON) {
                                $error = 'Function return type is void, but function contains return statement';
                                $phpcsFile->addError($error, $return, 'InvalidReturnVoid');
                            }
                        }
                    }//end if
                } else {
                    if ($returnType !== 'mixed' && \in_array('void', $typeNames, true) === false) {
                        // If return type is not void, there needs to be a return statement
                        // somewhere in the function that returns something.
                        if (isset($tokens[$stackPtr]['scope_closer']) === true) {
                            $endToken = $tokens[$stackPtr]['scope_closer'];
                            for ($returnToken = $stackPtr; $returnToken < $endToken; $returnToken++) {
                                if ($tokens[$returnToken]['code'] === T_CLOSURE
                                    || $tokens[$returnToken]['code'] === T_ANON_CLASS
                                ) {
                                    $returnToken = $tokens[$returnToken]['scope_closer'];
                                    continue;
                                }

                                if ($tokens[$returnToken]['code'] === T_RETURN
                                    || $tokens[$returnToken]['code'] === T_YIELD
                                    || $tokens[$returnToken]['code'] === T_YIELD_FROM
                                ) {
                                    break;
                                }
                            }

                            if ($returnToken === $endToken) {
                                $error = 'Function return type is not void, but function has no return statement';
                                $phpcsFile->addError($error, $return, 'InvalidNoReturn');
                            } else {
                                $semicolon = $phpcsFile->findNext(T_WHITESPACE, $returnToken + 1, null, true);

                                if ($tokens[$semicolon]['code'] === T_SEMICOLON) {
                                    $error = 'Function return type is not void, but function is returning void here';
                                    $phpcsFile->addError($error, $returnToken, 'InvalidReturnNotVoid');
                                }
                            }
                        }//end if
                    }
                }//end if
            }//end if
        } else {
            $error = 'Missing @return tag in function comment';
            $phpcsFile->addError($error, $tokens[$commentStart]['comment_closer'], 'MissingReturn');
        }//end if
    }

    /**
     * Process throws.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile
     * @param int $stackPtr
     * @param int $commentStart
     */
    protected function processThrows(File $phpcsFile, $stackPtr, $commentStart): void
    {
        $tokens = $phpcsFile->getTokens();

        foreach ($tokens[$commentStart]['comment_tags'] as $pos => $tag) {
            if ($tokens[$tag]['content'] !== '@throws') {
                continue;
            }

            $exception = null;
            $comment = null;
            if ($tokens[$tag + 2]['code'] === T_DOC_COMMENT_STRING) {
                $matches = [];
                \preg_match('/([^\s]+)(?:\s+(.*))?/', $tokens[$tag + 2]['content'], $matches);
                $exception = $matches[1];
                if (isset($matches[2]) === true && trim($matches[2]) !== '') {
                    $comment = $matches[2];
                }
            }

            if ($exception === null) {
                $error = 'Exception type missing for @throws tag in function comment';
                $phpcsFile->addError($error, $tag, 'InvalidThrows');
            } else {
                if ($comment === null) {
                    // Don't enforce comment for now
                    //$error = 'Comment missing for @throws tag in function comment';
                    //$phpcsFile->addError($error, $tag, 'EmptyThrows');
                } else {
                    // Any strings until the next tag belong to this comment.
                    if (isset($tokens[$commentStart]['comment_tags'][$pos + 1]) === true) {
                        $end = $tokens[$commentStart]['comment_tags'][$pos + 1];
                    } else {
                        $end = $tokens[$commentStart]['comment_closer'];
                    }

                    for ($i = $tag + 3; $i < $end; $i++) {
                        if ($tokens[$i]['code'] === T_DOC_COMMENT_STRING) {
                            $comment .= ' ' . $tokens[$i]['content'];
                        }
                    }

                    // Starts with a capital letter and ends with a full stop.
                    $firstChar = $comment[0];
                    if (\strtoupper($firstChar) !== $firstChar) {
                        $error = '@throws tag comment must start with a capital letter';
                        $phpcsFile->addError($error, $tag + 2, 'ThrowsNotCapital');
                    }

                    // Don't enforce the full stop for now
//                $lastChar = substr($comment, -1);
//                if ($lastChar !== '.') {
//                    $error = '@throws tag comment must end with a full stop';
//                    $phpcsFile->addError($error, ($tag + 2), 'ThrowsNoFullStop');
//                }
                }
            }//end if
        }//end foreach
    }

    /**
     * Check if a comment has a valid 'inheritdoc' annotation.
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     * @param int $commentStart
     * @param int $commentEnd
     *
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException
     */
    protected function validateInheritdoc(File $phpcsFile, int $stackPtr, int $commentStart, int $commentEnd): bool
    {
        $commentString = $phpcsFile->getTokensAsString($commentStart, $commentEnd - $commentStart + 1);

        if (\preg_match('/\@inheritdoc/', $commentString)) {
            // Ignore anonymous class for now
            $tokens = $phpcsFile->getTokens();
            if (\in_array(T_ANON_CLASS, $tokens[$commentStart]['conditions'] ?? [], true)) {
                return true;
            }

            $classes = $this->getClassParentsAndInterfaces();

            if ($classes !== false) {
                $method = $phpcsFile->getDeclarationName($stackPtr);
                foreach ($classes as $class) {
                    if (\method_exists($class, $method)) {
                        return true;
                    }
                }
                $error = 'No override method found for {@inheritdoc} annotation';
                $phpcsFile->addError($error, $commentStart, 'InvalidInheritdoc');
            } else {
                return true;
            }
        }

        return false;
    }

    //end processReturn()

    /**
     * Get class parents and interfaces.
     * Returns array of class and interface names or false if the class cannot be loaded.
     *
     * @return mixed[]|bool
     *
     * @throws \PHP_CodeSniffer\Exceptions\RuntimeException
     */
    private function getClassParentsAndInterfaces()
    {
        $phpcsFile = $this->phpcsFile;
        $tokens = $phpcsFile->getTokens();
        $nsStart = $phpcsFile->findNext([T_NAMESPACE], 0);
        $class = '';

        // Set the default return value.
        $this->parentsAndInterfaces = false;

        // Build the namespace.
        if ($nsStart !== false) {
            $nsEnd = $phpcsFile->findNext([T_SEMICOLON], $nsStart + 2);
            for ($i = $nsStart + 2; $i < $nsEnd; $i++) {
                $class .= $tokens[$i]['content'];
            }
            $class .= '\\';
        } else {
            $nsEnd = 0;
        }

        // Find the class/interface declaration.
        $classPtr = $phpcsFile->findNext([T_CLASS, T_INTERFACE], $nsEnd);

        if ($classPtr !== false) {
            $class .= $phpcsFile->getDeclarationName($classPtr);

            if (\class_exists($class) || \interface_exists($class)) {
                $this->parentsAndInterfaces = \array_merge(
                    \class_parents($class),
                    \class_implements($class),
                    \class_uses($class)
                );
            }
        }

        return $this->parentsAndInterfaces;
    }
}
