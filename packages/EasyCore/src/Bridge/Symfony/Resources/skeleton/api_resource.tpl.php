<?= "<?php\n" ?>
<?= "declare(strict_types=1);\n" ?>

namespace <?= $namespace ?>;

use ApiPlatform\Core\Annotation\ApiResource;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces\NoIriItemInterface;

/**
 * @ApiResource(
 *     collectionOperations={"post"},
 *     itemOperations={},
 *     normalizationContext={"groups"={"<?= $snakeCaseName ?>:read"}},
 *     denormalizationContext={"groups"={"<?= $snakeCaseName ?>:write"}}
 * )
 */
final class <?= $class_name ?> implements NoIriItemInterface
{

}
