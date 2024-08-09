<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Fixture\App\Processor;

use EonX\EasyDoctrine\Common\EntityManager\EntityManagerAwareTrait;

final class WithEntityManagerProcessor
{
    use EntityManagerAwareTrait;
}
