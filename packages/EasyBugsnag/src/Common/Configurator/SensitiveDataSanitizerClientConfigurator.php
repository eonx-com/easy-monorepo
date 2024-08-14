<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Common\Configurator;

use Bugsnag\Client;
use Bugsnag\Report;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;

final class SensitiveDataSanitizerClientConfigurator extends AbstractClientConfigurator
{
    public function __construct(
        private readonly SensitiveDataSanitizerInterface $sensitiveDataSanitizer,
    ) {
        parent::__construct(\PHP_INT_MAX);
    }

    public function configure(Client $bugsnag): void
    {
        $sanitizer = $this->sensitiveDataSanitizer;

        $bugsnag->registerCallback(static function (Report $report) use ($sanitizer): void {
            $report->setMetaData($sanitizer->sanitize($report->getMetaData()), false);

            $breadcrumbs = $report->toArray()['breadcrumbs'] ?? [];
            $redactedBreadcrumbs = [];

            foreach ($breadcrumbs as $breadcrumb) {
                if (isset($breadcrumb['metaData'])) {
                    $breadcrumb['metaData'] = $sanitizer->sanitize($breadcrumb['metaData']);
                }

                $redactedBreadcrumbs[] = $breadcrumb;
            }

            (function ($property, $value): void {
                $this->{$property} = $value;
            })->call($report, 'breadcrumbs', $redactedBreadcrumbs);
        });
    }
}
