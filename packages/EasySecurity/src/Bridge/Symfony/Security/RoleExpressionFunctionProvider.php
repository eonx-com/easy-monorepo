<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Security;

use BackedEnum;
use EonX\EasySecurity\Bridge\Symfony\Exceptions\RoleConstantNotFoundException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Throwable;

final class RoleExpressionFunctionProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @var string[]
     */
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
                'role',
                static function (): void {
                },
                function ($params, string $role): string {
                    if (isset($this->cached[$role])) {
                        return $this->cached[$role];
                    }

                    foreach ($this->locations as $location) {
                        $constant = \sprintf('%s::%s', $location, $role);

                        try {
                            $value = \constant($constant);

                            if ($value instanceof BackedEnum) {
                                $value = $value->value;
                            }

                            $this->cached[$role] = $value;

                            return $this->cached[$role];
                        } catch (Throwable) {
                            $this->logger->info(\sprintf('Constant "%s" not found', $constant));
                        }
                    }

                    throw new RoleConstantNotFoundException(
                        \sprintf('Constant for role "%s" not found', $role)
                    );
                }
            ),
        ];
    }
}
