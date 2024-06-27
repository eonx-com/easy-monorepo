<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Unit\Bundle;

use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Common\Strategy\VerboseStrategyInterface;
use EonX\EasyErrorHandler\Tests\Unit\Common\Builder\TestRenderWithDefaultBuildersDataProvider;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

final class ErrorHandlerTest extends AbstractSymfonyTestCase
{
    #[DataProviderExternal(TestRenderWithDefaultBuildersDataProvider::class, 'provide')]
    public function testRenderWithDefaultBuilders(
        Request $request,
        Throwable $exception,
        callable $assertResponse,
        ?array $translations = null,
    ): void {
        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = self::getService(TranslatorInterface::class);
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', $this->prepareTranslations($translations), 'en', 'violations');

        $sut = self::getService(ErrorHandlerInterface::class);

        $assertResponse($sut->render($request, $exception));
    }

    #[DataProviderExternal(TestRenderWithDefaultBuildersDataProvider::class, 'provideWithExtendedResponse')]
    public function testRenderWithDefaultBuildersAndExtendedResponse(
        Request $request,
        Throwable $exception,
        callable $assertResponse,
        ?array $translations = null,
    ): void {
        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = self::getService(TranslatorInterface::class);
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', $this->prepareTranslations($translations), 'en', 'violations');
        $chainVerboseStrategy = self::getService(VerboseStrategyInterface::class);
        self::setPrivatePropertyValue($chainVerboseStrategy, 'verbose', true);

        $sut = self::getService(ErrorHandlerInterface::class);

        $assertResponse($sut->render($request, $exception));
    }

    private function prepareTranslations(?array $translations = null): array
    {
        $result = [];
        foreach ($translations ?? [] as $key => $value) {
            $result[$key] = \str_replace('$', '', (string)$value);
        }

        return $result;
    }
}
