<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Generator;

use EonX\EasyRandom\Exception\InvalidRandomStringException;
use EonX\EasyRandom\ValueObject\RandomStringConfig;
use Symfony\Component\String\ByteString;

final class RandomStringGenerator implements RandomStringGeneratorInterface
{
    public function generate(RandomStringConfig $randomStringConfig): string
    {
        $attempts = 0;

        do {
            $randomString = ByteString::fromRandom(
                $randomStringConfig->resolveLength(),
                $randomStringConfig->resolveAlphabet()
            )->toString();
            $attempts++;
        } while (
            $this->validateString($randomString, $randomStringConfig) === false
            && $attempts < $randomStringConfig->getMaxAttempts()
        );

        if ($attempts === $randomStringConfig->getMaxAttempts()) {
            throw new InvalidRandomStringException(\sprintf(
                'Could not generate valid random string for alphabet "%s"',
                $randomStringConfig->resolveAlphabet()
            ));
        }

        if ($randomStringConfig->getPrefix() !== null) {
            $randomString = $randomStringConfig->getPrefix() . $randomString;
        }

        if ($randomStringConfig->getSuffix() !== null) {
            $randomString .= $randomStringConfig->getSuffix();
        }

        return $randomString;
    }

    private function validateString(string $randomString, RandomStringConfig $randomStringConfig): bool
    {
        if ($randomStringConfig->getConstraints() === null) {
            return true;
        }

        foreach ($randomStringConfig->getConstraints() as $constraint) {
            if ($constraint->isValid($randomString) === false) {
                return false;
            }
        }

        return true;
    }
}
