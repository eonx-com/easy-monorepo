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

            // The context holds the request path, e.g. "GET /reset-password/token/<value>"
            $context = $report->getContext();

            if (\is_string($context)) {
                $report->setContext($sanitizer->sanitize($context));
            }

            // Exception messages (the report and its previous reports) are sent as they are,
            // e.g. 'HTTP 401 returned for "https://example.com?token=<value>"'
            (function () use ($sanitizer): void {
                for ($current = $this; $current !== null; $current = $current->previous) {
                    if (\is_string($current->message)) {
                        $current->message = $sanitizer->sanitize($current->message);
                    }
                }
            })->call($report);

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
