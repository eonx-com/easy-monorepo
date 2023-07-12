<?php

declare(strict_types=1);

namespace EonX\EasyUtils\StringTrimmers;

final class RecursiveStringTrimmer implements StringTrimmerInterface
{
    /**
     * @var string[]
     */
    private array $exceptKeys;

    public function trim(mixed $data, ?array $exceptKeys = null): mixed
    {
        $this->exceptKeys = $exceptKeys ?? [];

        if (\is_array($data)) {
            return $this->cleanArray($data);
        }

        return $this->transform($data, '');
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    private function cleanArray(array $data, ?string $keyPrefix = null): array
    {
        foreach ($data as $key => $value) {
            if (\is_array($value) === false) {
                $data[$key] = $this->transform($value, $keyPrefix . $key);

                continue;
            }

            if (\in_array($keyPrefix . $key, $this->except, true) === false) {
                $data[$key] = $this->cleanArray($value, $keyPrefix . $key . '.');
            }
        }

        return $data;
    }

    private function transform(mixed $value, string $key): mixed
    {
        if (\in_array($key, $this->exceptKeys, true)) {
            return $value;
        }

        return \is_string($value) ? \trim($value) : $value;
    }
}
