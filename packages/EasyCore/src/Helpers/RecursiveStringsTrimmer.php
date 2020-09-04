<?php

declare(strict_types=1);

namespace EonX\EasyCore\Helpers;

final class RecursiveStringsTrimmer implements StringsTrimmerInterface
{
    /**
     * A list of array keys whose values will be ignored during processing.
     *
     * @var string[]
     */
    private $except;

    /**
     * {@inheritdoc}
     */
    public function trim($data, ?array $except = null)
    {
        $this->except = $except ?? [];

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

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private function transform($value, string $key)
    {
        if (\in_array($key, $this->except, true)) {
            return $value;
        }

        return \is_string($value) ? \trim($value) : $value;
    }
}
