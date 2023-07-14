<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Bridge\Symfony\DataCollector;

use EonX\EasyDecision\Exceptions\ContextNotSetException;
use EonX\EasyDecision\Interfaces\DecisionFactoryInterface;
use EonX\EasyDecision\Interfaces\DecisionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

final class DecisionDataCollector extends DataCollector
{
    /**
     * @var string
     */
    public const NAME = 'easy_decision.decision_collector';

    public function __construct(
        private DecisionFactoryInterface $decisionFactory,
    ) {
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        $this->data['decisions'] = $this->mapDecisions();
    }

    /**
     * @return mixed[]
     */
    public function getDecisions(): array
    {
        return $this->data['decisions'] ?? [];
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function reset(): void
    {
        $this->data = [];
    }

    /**
     * @return mixed[]
     */
    private function mapConfigurators(DecisionInterface $decision): array
    {
        $configurators = [];

        foreach ($this->decisionFactory->getConfiguratorsByDecision($decision) as $configurator) {
            $reflection = new \ReflectionClass($configurator);

            $configurators[] = [
                'priority' => $configurator->getPriority(),
                'class' => $reflection->getName(),
                'filename' => $reflection->getFileName(),
            ];
        }

        return $configurators;
    }

    /**
     * @return mixed[]
     */
    private function mapDecisions(): array
    {
        $decisions = [];

        foreach ($this->decisionFactory->getConfiguredDecisions() as $decision) {
            try {
                $context = $decision->getContext();
            } catch (ContextNotSetException $exception) {
                $context = null;
            }

            $decisions[] = [
                'name' => $decision->getName(),
                'context' => [
                    'decision_type' => $context ? $context->getDecisionType() : \get_class($decision),
                    'original_input' => $context ? $context->getOriginalInput() : 'Decision not made...',
                    'rule_output' => $context ? $context->getRuleOutputs() : [],
                ],
                'configurators' => $this->mapConfigurators($decision),
            ];
        }

        return $decisions;
    }
}
