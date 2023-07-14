<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Configurators;

use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractFromHeaderConfigurator extends AbstractSecurityContextConfigurator
{
    /**
     * @param string[] $headerNames
     */
    public function __construct(
        private array $headerNames,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    public function configure(SecurityContextInterface $context, Request $request): void
    {
        $header = $this->getHeaderValue($request);

        if (empty($header)) {
            return;
        }

        $this->doConfigure($context, $request, $header);
    }

    abstract protected function doConfigure(SecurityContextInterface $context, Request $request, string $header): void;

    private function getHeaderValue(Request $request): ?string
    {
        foreach ($this->headerNames as $headerName) {
            $header = $request->headers->get($headerName);

            if (empty($header) === false) {
                return $header;
            }
        }

        return null;
    }
}
