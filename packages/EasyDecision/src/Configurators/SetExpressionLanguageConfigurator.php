<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Configurators;

use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageConfigInterface;
use EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageFactoryInterface;
use EonX\EasyDecision\Interfaces\DecisionInterface;

final class SetExpressionLanguageConfigurator extends AbstractConfigurator
{
    /**
     * @var \EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageConfigInterface
     */
    private $config;

    /**
     * @var \EonX\EasyDecision\Expressions\Interfaces\ExpressionLanguageFactoryInterface
     */
    private $exprLangFactory;

    public function __construct(
        ExpressionLanguageFactoryInterface $exprLangFactory,
        ExpressionLanguageConfigInterface $config = null,
        ?int $priority = null
    )
    {
        $this->exprLangFactory = $exprLangFactory;
        $this->config = $config;

        parent::__construct($priority);
    }

    public function configure(DecisionInterface $decision): void
    {
        if ($decision->getExpressionLanguage() !== null) {
            return;
        }

        $decision->setExpressionLanguage($this->exprLangFactory->create($this->config));
    }
}
