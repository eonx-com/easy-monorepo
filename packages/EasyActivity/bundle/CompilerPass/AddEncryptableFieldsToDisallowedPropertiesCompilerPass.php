<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Bundle\CompilerPass;

use EonX\EasyActivity\Bundle\Enum\ConfigParam as EasyActivityConfigParam;
use EonX\EasyEncryption\Encryptable\Attribute\EncryptableField;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class AddEncryptableFieldsToDisallowedPropertiesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($this->isBundleEnabled('EasyEncryptionBundle', $container) === false) {
            return;
        }

        if (\class_exists(EncryptableField::class) === false) {
            return;
        }

        if ($container->hasParameter(EasyActivityConfigParam::Subjects->value) === false) {
            return;
        }

        /** @var array<class-string, array> $subjects */
        $subjects = $container->getParameter(EasyActivityConfigParam::Subjects->value);

        foreach ($subjects as $subjectClass => $subjectConfig) {
            $encryptableFieldNames = $this->getEncryptableFieldNames($container, $subjectClass);

            if ($encryptableFieldNames === []) {
                continue;
            }

            // Merge with existing disallowed_properties
            $existingDisallowedProperties = $subjectConfig['disallowed_properties'] ?? [];
            $mergedDisallowedProperties = \array_unique([
                ...$existingDisallowedProperties,
                ...$encryptableFieldNames,
            ]);

            // Update the subject configuration
            $subjects[$subjectClass]['disallowed_properties'] = $mergedDisallowedProperties;
        }

        // Update the container parameter with modified subjects
        $container->setParameter(EasyActivityConfigParam::Subjects->value, $subjects);
    }

    /**
     * @param class-string $className
     *
     * @return array<string>
     */
    private function getEncryptableFieldNames(ContainerBuilder $container, string $className): array
    {
        $encryptableFieldNames = [];
        $reflectionClass = $container->getReflectionClass($className);

        if ($reflectionClass === null) {
            return [];
        }

        do {
            foreach ($reflectionClass->getProperties() as $property) {
                $attributes = $property->getAttributes(EncryptableField::class);

                if ($attributes !== []) {
                    $encryptableFieldNames[] = $property->getName();
                }
            }

            $reflectionClass = $reflectionClass->getParentClass();
        } while ($reflectionClass !== false);

        return \array_unique($encryptableFieldNames);
    }

    private function isBundleEnabled(string $bundleName, ContainerBuilder $container): bool
    {
        if ($container->hasParameter('kernel.bundles') === false) {
            return false;
        }

        /** @var array<string, class-string> $bundles */
        $bundles = $container->getParameter('kernel.bundles');

        return isset($bundles[$bundleName]);
    }
}
