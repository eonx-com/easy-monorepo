<?= "<?php\n" ?>
<?= "declare(strict_types=1);\n" ?>

namespace <?= $namespace ?>;

use <?= $resourceFcqn ?>;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\AbstractSimpleDataPersister;

final class <?= $class_name ?> extends AbstractSimpleDataPersister
{
    public function getApiResourceClass(): string
    {
        return <?= $resourceShortName ?>::class;
    }

    /**
     * @param \<?= $resourceFcqn ?> $data
     *
     * @return \<?= $resourceFcqn . "\n" ?>
     */
    public function persist($data)
    {
        // TODO - Implement persist...

        return $data;
    }
}
