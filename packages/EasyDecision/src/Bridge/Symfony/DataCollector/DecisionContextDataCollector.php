<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Bridge\Symfony\DataCollector;

use EonX\EasyDecision\Interfaces\ContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

final class DecisionContextDataCollector extends DataCollector
{
    /**
     * @const string
     */
    public const NAME = 'easy_decision.decision_context_collector';

    /**
     * @var \EonX\EasyDecision\Interfaces\ContextInterface
     */
    private $decisionContext;

    public function __construct(ContextInterface $decisionContext)
    {
        $this->decisionContext = $decisionContext;
    }

    public function collect(Request $request, Response $response): void
    {
        $this->data['decision_type'] = $this->decisionContext->getDecisionType();
        $this->data['original_input'] = $this->decisionContext->getOriginalInput();
        $this->data['propagation_stopped'] = $this->decisionContext->isPropagationStopped();

        $this->setRuleOutputs();
    }

    public function getDecisionType(): ?string
    {
        return $this->data['decision_type'] ?? null;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getOriginalInput(): ?string
    {
        return $this->data['original_input'] ?? null;
    }

    public function getPropagationStopped(): string
    {
        return ($this->data['propagation_stopped'] ?? null) === true ? 'Yes' : 'No';
    }

    /**
     * @return mixed[]
     */
    public function getRuleOutputs(): array
    {
        return $this->data['rule_outputs'] ?? [];
    }

    public function reset(): void
    {
        $this->data = [];
    }

    private function setRuleOutputs(): void
    {
        $ruleOutputs = [];

        foreach ($this->decisionContext->getRuleOutputs() as $rule => $output) {
            $ruleOutputs[] = [\compact('rule', 'output')];
        }

        $this->data['rule_outputs'] = $ruleOutputs;
    }
}
