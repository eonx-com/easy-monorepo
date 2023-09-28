<?php
declare(strict_types=1);

namespace EonX\EasyTest\Faker\Providers;

use EonX\EasyTest\Faker\Generators\UniqueGroupGenerator;
use EonX\EasyTest\Faker\Generators\UniqueGroupPropertyValueGenerator;
use Faker\Provider\Text as BaseProvider;
use LogicException;

final class UniqueGroupProvider extends BaseProvider
{
    private const ITERATIONS_LIMIT = 3000;

    private array $generatedUniqueGroupsValues = [];

    /**
     * @var array<string, \EonX\EasyTest\Faker\Generators\UniqueGroupGenerator> $uniqueGroupGenerators
     */
    private array $uniqueGroupGenerators = [];

    public function clearUniqueGroups(): void
    {
        $this->generatedUniqueGroupsValues = [];
    }

    public function processUniqueGroups(array $attributes, string $factoryFqcn): array
    {
        $uniqueParametersGroups = $this->getUniqueParametersGroups($attributes);
        if (\count($uniqueParametersGroups) === 0) {
            return $attributes;
        }
        $uniqueParams = [];
        foreach ($uniqueParametersGroups as $groupName => $groupAttributes) {
            $uniqueParams += $this->generateUniqueParams($factoryFqcn, $attributes, $groupName, $groupAttributes);
        }

        return \array_merge($attributes, $uniqueParams);
    }

    /**
     * With the unique generator you are guaranteed to never get the same combination of values within the same group.
     *
     * <code>
     * $faker->uniqueGroup('group-name')->randomElement(array(1, 2, 3));
     * </code>.
     */
    public function uniqueGroup(string $groupName): UniqueGroupGenerator
    {
        return $this->uniqueGroupGenerators[$groupName] ??= new UniqueGroupGenerator($this->generator, $groupName);
    }

    /**
     * @param array<int, string> $groupAttributes
     */
    private function generateUniqueParams(
        string $factoryFqcn,
        array $attributes,
        string $groupName,
        array $groupAttributes,
    ): array {
        $found = false;
        $uniqueParams = [];
        $this->generatedUniqueGroupsValues[$factoryFqcn][$groupName] ??= [];
        $uniqueGroupParameters = $this->generatedUniqueGroupsValues[$factoryFqcn][$groupName];
        $callableParams = \array_intersect_key($attributes, \array_flip($groupAttributes));
        for ($i = 0; $i < self::ITERATIONS_LIMIT; $i++) {
            $uniqueParams = \array_map(
                static fn (UniqueGroupPropertyValueGenerator $generator) => $generator->generateValue(),
                $callableParams
            );
            $paramsKey = \implode(',', $uniqueParams);

            if (\in_array($paramsKey, $uniqueGroupParameters, true) === false) {
                $found = true;

                break;
            }
        }

        if ($found === false) {
            throw new LogicException(\sprintf(
                'Can not generate unique parameters [%s] group due %d iterations',
                \implode(', ', $groupAttributes),
                self::ITERATIONS_LIMIT
            ));
        }

        $this->generatedUniqueGroupsValues[$factoryFqcn][$groupName][] = \implode(',', $uniqueParams);

        return $uniqueParams;
    }

    private function getUniqueParametersGroups(array $attributes): array
    {
        $groups = [];
        foreach ($attributes as $name => $attribute) {
            if ($attribute instanceof UniqueGroupPropertyValueGenerator !== true) {
                continue;
            }
            $groups[$attribute->getGroupName()] ??= [];
            $groups[$attribute->getGroupName()][] = $name;
        }

        return $groups;
    }
}
