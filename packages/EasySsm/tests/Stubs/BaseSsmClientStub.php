<?php

declare(strict_types=1);

namespace EonX\EasySsm\Tests\Stubs;

use Aws\Ssm\SsmClient;

final class BaseSsmClientStub extends SsmClient
{
    /**
     * @var mixed[]
     */
    private $actions = [
        'put' => [],
        'delete' => [],
    ];

    /**
     * @var mixed[]
     */
    private $paginatorCalls = [];

    /**
     * @var mixed[]
     */
    private $parameters;

    /**
     * @param null|mixed[] $parameters
     */
    public function __construct(?array $parameters = null)
    {
        $this->parameters = $parameters ?? [];

        // Parent not called on purpose.
    }

    /**
     * @param mixed[] $args
     */
    public function deleteParameter(array $args): void
    {
        $this->actions['delete'][] = $args;
    }

    /**
     * @return mixed[]
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @param string $name
     * @param null|mixed[] $args
     *
     * @return mixed[]
     */
    public function getPaginator($name, ?array $args = null): array
    {
        $this->paginatorCalls[$name] = $args ?? [];

        return [[
            'Parameters' => $this->parameters,
        ]];
    }

    /**
     * @return mixed[]
     */
    public function getPaginatorCalls(): array
    {
        return $this->paginatorCalls;
    }

    /**
     * @param mixed[] $args
     */
    public function putParameter(array $args): void
    {
        $this->actions['put'][] = $args;
    }
}
