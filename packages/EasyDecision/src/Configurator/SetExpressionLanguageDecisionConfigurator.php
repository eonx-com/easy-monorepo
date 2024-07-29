<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Configurator;

use EonX\EasyDecision\Decision\DecisionInterface;
use EonX\EasyDecision\Factory\ExpressionLanguageFactoryInterface;

final class SetExpressionLanguageDecisionConfigurator extends AbstractDecisionConfigurator
{
    public function __construct(
        private readonly ExpressionLanguageFactoryInterface $exprLangFactory,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function configure(DecisionInterface $decision): void
    {
        if ($decision->getExpressionLanguage() !== null) {
            return;
        }

        $decision->setExpressionLanguage($this->exprLangFactory->create());
    }
}
