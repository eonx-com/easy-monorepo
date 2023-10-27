<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Security;

use EonX\EasySecurity\Bridge\Symfony\Exceptions\PermissionConstantNotFoundException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Throwable;
use UnitEnum;

final class PermissionExpressionFunctionProvider implements ExpressionFunctionProviderInterface
{
    private array $cached = [];

    /**
     * @param string[] $locations
     */
    public function __construct(
        private array $locations,
        private LoggerInterface $logger = new NullLogger(),
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

                            if ($value instanceof UnitEnum) {
                                return $value->value;
                            }

                            return $value;
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
