<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\Listeners;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

use function Symfony\Component\String\u;

final class StaticPhpFileListener extends AbstractRequestEventListener
{
    /**
     * @param string[] $allowedDirs
     * @param string[] $allowedFilenames
     */
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly array $allowedDirs,
        private readonly array $allowedFilenames
    ) {
    }

    protected function doInvoke(RequestEvent $event): void
    {
        $pathInfo = $event->getRequest()
            ->getPathInfo();

        if (u($pathInfo)->endsWith('.php') === false
            || \in_array($pathInfo, $this->allowedFilenames, true) === false) {
            return;
        }

        foreach ($this->allowedDirs as $dir) {
            $filename = \sprintf('%s%s', $dir, $pathInfo);

            if ($this->filesystem->exists($filename)) {
                // Require will trigger any output from the file which will be captured in the $bufferedOutput
                // and added to the response. Alternatively the file can return a Response instance, and it will
                // be used instead.
                $response = require $filename;

                $event->setResponse($response instanceof Response ? $response : new Response());

                return;
            }
        }
    }
}
