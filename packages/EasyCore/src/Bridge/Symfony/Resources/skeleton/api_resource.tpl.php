<?= "<?php\n" ?>
<?= "declare(strict_types=1);\n" ?>

namespace <?= $namespace ?>;

use ApiPlatform\Core\Annotation\ApiResource;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces\NoIriItemInterface;

/**
 * @ApiResource(
 *     collectionOperations={"post"},
 *     itemOperations={},
 *     normalizationContext={"groups"={"<?= $snakeCaseName ?>:write"}},
 *     denormalizationContext={"groups"={"<?= $snakeCaseName ?>:read"}}
 * )
 */
final class <?= $class_name ?> implements NoIriItemInterface
{

}
