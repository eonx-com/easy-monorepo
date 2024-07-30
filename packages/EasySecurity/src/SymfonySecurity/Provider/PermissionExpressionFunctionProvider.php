<?php
declare(strict_types=1);

namespace EonX\EasySecurity\SymfonySecurity\Provider;

use BackedEnum;
use EonX\EasySecurity\SymfonySecurity\Exception\PermissionConstantNotFoundException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Throwable;

final class PermissionExpressionFunctionProvider implements ExpressionFunctionProviderInterface
{
    private array $cached = [];

    /**
     * @param string[] $locations
     */
    public function __construct(
        private readonly array $locations,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    /**
     * @return \Symfony\Component\ExpressionLanguage\ExpressionFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'permission',
                static function (): void {
                },
                function ($params, string $permission): string {
                    if (isset($this->cached[$permission])) {
                        return $this->cached[$permission];
                    }

                    foreach ($this->locations as $location) {
                        $constant = \sprintf('%s::%s', $location, $permission);

                        try {
                            $value = \constant($constant);

                            if ($value instanceof BackedEnum) {
                                $value = $value->value;
                            }

                            $this->cached[$permission] = $value;

                            return $this->cached[$permission];
                        } catch (Throwable) {
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
