<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Configurators;

use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageInterface;
use EonX\EasyDecision\Interfaces\DecisionInterface;

final class SetExpressionLanguageConfigurator extends AbstractConfigurator
{
    /**
     * @var \EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageInterface
     */
    private $expressionLanguage;

    public function __construct(ExpressionLanguageInterface $expressionLanguage, ?int $priority = null)
    {
        $this->expressionLanguage = $expressionLanguage;

        parent::__construct($priority);
    }

    public function configure(DecisionInterface $decision): void
    {
        if ($decision->getExpressionLanguage() !== null) {
            return;
        }

        $decision->setExpressionLanguage($this->expressionLanguage);
    }
}
