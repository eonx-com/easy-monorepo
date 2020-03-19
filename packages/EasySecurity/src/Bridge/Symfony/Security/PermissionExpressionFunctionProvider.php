<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Security;

use EonX\EasySecurity\Bridge\Symfony\Exceptions\PermissionConstantNotFoundException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

final class PermissionExpressionFunctionProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @var mixed[]
     */
    private $cached = [];

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var string[]
     */
    private $targets;

    /**
     * @param string[] $targets
     */
    public function __construct(array $targets, ?LoggerInterface $logger = null)
    {
        $this->targets = $targets;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @return ExpressionFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'permission',
                function (): void {
                },
                function ($params, string $permission): string {
                    if (isset($this->cached[$permission])) {
                        return $this->cached[$permission];
                    }

                    foreach ($this->targets as $target) {
                        $constant = \sprintf('%s::%s', $target, $permission);

                        try {
                            return \constant($constant);
                        } catch (\Throwable $throwable) {
                            $this->logger->info(\sprintf('Constant "%s" not found', $constant));
                        }
                    }

                    throw new PermissionConstantNotFoundException(
                        \sprintf('Constant for permission "%s" not found', $permission)
                    );
                }
            ),
        ];
    }
}
