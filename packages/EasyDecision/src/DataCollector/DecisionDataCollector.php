<?php
declare(strict_types=1);

namespace EonX\EasyDecision\DataCollector;

use EonX\EasyDecision\Decision\DecisionInterface;
use EonX\EasyDecision\Exception\ContextNotSetException;
use EonX\EasyDecision\Factory\DecisionFactoryInterface;
use EonX\EasyUtils\Common\DataCollector\AbstractDataCollector;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class DecisionDataCollector extends AbstractDataCollector
{
    public function __construct(
        private readonly DecisionFactoryInterface $decisionFactory,
    ) {
    }

    public function collect(Request $request, Response $response, ?Throwable $exception = null): void
    {
        $this->data['decisions'] = $this->mapDecisions();
    }

    public function getDecisions(): array
    {
        return $this->data['decisions'] ?? [];
    }

    private function mapConfigurators(DecisionInterface $decision): array
    {
        $configurators = [];

        foreach ($this->decisionFactory->getConfiguratorsByDecision($decision) as $configurator) {
            $reflection = new ReflectionClass($configurator);

            $configurators[] = [
                'class' => $reflection->getName(),
                'filename' => $reflection->getFileName(),
                'priority' => $configurator->getPriority(),
            ];
        }

        return $configurators;
    }

    private function mapDecisions(): array
    {
        $decisions = [];

        foreach ($this->decisionFactory->getConfiguredDecisions() as $decision) {
            try {
                $context = $decision->getContext();
            } catch (ContextNotSetException) {
                $context = null;
            }

            $decisions[] = [
                'configurators' => $this->mapConfigurators($decision),
                'context' => [
                    'decision_type' => $context !== null ? $context->getDecisionType() : $decision::class,
                    'original_input' => $context !== null ? $context->getOriginalInput() : 'Decision not made...',
                    'rule_output' => $context !== null ? $context->getRuleOutputs() : [],
                ],
                'name' => $decision->getName(),
            ];
        }

        return $decisions;
    }
}
