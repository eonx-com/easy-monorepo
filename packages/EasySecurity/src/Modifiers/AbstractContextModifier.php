<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Modifiers;

use EonX\EasyApiToken\Exceptions\InvalidArgumentException;
use EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;
use EonX\EasySecurity\Interfaces\ContextModifierInterface;

abstract class AbstractContextModifier implements ContextModifierInterface
{
    /**
     * @var null|int
     */
    private $priority;

    public function __construct(?int $priority = null)
    {
        $this->priority = $priority;
    }

    public function getPriority(): int
    {
        return $this->priority ?? 0;
    }

    /**
     * @param null|mixed $default
     *
     * @return null|mixed
     *
     * @throws \Nette\Utils\JsonException
     */
    protected function getClaimSafely(JwtEasyApiTokenInterface $token, string $claim, $default = null)
    {
        try {
            return $token->getClaimForceArray($claim);
        } catch (InvalidArgumentException $exception) {
            // TODO - Should we do something with this exception?
            return $default;
        }
    }
}
