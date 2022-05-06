<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\EasyUtils;

use Bugsnag\Client;
use Bugsnag\Report;
use EonX\EasyBugsnag\Configurators\AbstractClientConfigurator;
use EonX\EasyUtils\SensitiveData\SensitiveDataSanitizerInterface;

final class SensitiveDataConfigurator extends AbstractClientConfigurator
{
    public function __construct(private readonly SensitiveDataSanitizerInterface $sensitiveDataSanitizer)
    {
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
