<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Application\Twig;

use EonX\EasyApiPlatform\Tests\Application\AbstractApplicationTestCase;

final class TemplateOverrideTest extends AbstractApplicationTestCase
{
    public function testTemplateOverride(): void
    {
        /** @var \Twig\Environment $twig */
        $twig = self::getService('twig');
        /** @var \Twig\Loader\FilesystemLoader $loader */
        $loader = $twig->getLoader();

        self::assertStringContainsString(
            'EasyApiPlatform/bundle/templates/bundles/ApiPlatformBundle',
            $loader->getPaths('ApiPlatform')[0]
        );
    }
}
