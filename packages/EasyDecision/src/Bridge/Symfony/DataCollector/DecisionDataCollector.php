<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Bridge\Symfony\DataCollector;

use EonX\EasyDecision\Interfaces\DecisionAggregatorInterface;
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

    /**
     * @var \EonX\EasyDecision\Interfaces\DecisionAggregatorInterface
     */
    private $decisionAggregator;

    public function __construct(DecisionAggregatorInterface $decisionAggregator)
    {
        $this->decisionAggregator = $decisionAggregator;
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        $this->data['decisions'] = $this->mapDecisions();
    }

    /**
     * @return \EonX\EasyDecision\Interfaces\DecisionInterface[]
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

        foreach ($this->decisionAggregator->getConfiguratorsByDecision($decision) as $configurator) {
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

        foreach ($this->decisionAggregator->getDecisions() as $decision) {
            $context = $decision->getContext();

            $decisions[] = [
                'name' => $decision->getName(),
                'context' => [
                    'decision_type' => $context->getDecisionType(),
                    'original_input' => $context->getOriginalInput(),
                    'rule_output' => $context->getRuleOutputs(),
                ],
                'configurators' => $this->mapConfigurators($decision),
            ];
        }

        return $decisions;
    }
}
