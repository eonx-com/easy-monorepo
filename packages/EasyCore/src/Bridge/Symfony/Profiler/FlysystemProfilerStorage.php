<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Profiler;

use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\HttpKernel\Profiler\ProfilerStorageInterface;

final class FlysystemProfilerStorage implements ProfilerStorageInterface
{
    /**
     * @var string
     */
    private const INDEX_FILENAME = 'index.csv';

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    private $filesystem;

    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Finds profiler tokens for the given criteria.
     *
     * @param string $ip The IP
     * @param string $url The URL
     * @param string|null $limit The maximum number of tokens to return
     * @param string|null $method The request method
     * @param int|null $start The start date to search from
     * @param int|null $end The end date to search to
     *
     * @return mixed[] An array of tokens
     *
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function find($ip, $url, $limit, $method, $start = null, $end = null, ?string $statusCode = null): array
    {
        // If index file doesn't exist, abort
        if ($this->filesystem->has(self::INDEX_FILENAME) === false) {
            return [];
        }

        $contents = \explode(\PHP_EOL, (string)$this->filesystem->read(self::INDEX_FILENAME));
        $results = [];

        foreach ($contents as $line) {
            // Skip empty lines
            if (empty($line)) {
                continue;
            }

            $values = \str_getcsv($line);
            [$csvToken, $csvIp, $csvMethod, $csvUrl, $csvTime, $csvParent, $csvStatusCode] = $values;
            $csvTime = (int)$csvTime;

            if (($ip && \str_contains((string)$csvIp, $ip) === false)
                || ($url && \str_contains((string)$csvUrl, $url) === false)
                || ($method && \str_contains((string)$csvMethod, $method) === false)
                || ($statusCode && \str_contains((string)$csvStatusCode, $statusCode) === false)) {
                continue;
            }

            if (empty($start) === false && $csvTime < $start) {
                continue;
            }

            if (empty($end) === false && $csvTime > $end) {
                continue;
            }

            $results[$csvToken] = [
                'token' => $csvToken,
                'ip' => $csvIp,
                'method' => $csvMethod,
                'url' => $csvUrl,
                'time' => $csvTime,
                'parent' => $csvParent,
                'status_code' => $csvStatusCode,
            ];
        }

        return \array_values($results);
    }

    public function purge(): void
    {
        foreach ($this->filesystem->listContents() as $content) {
            $method = $content['type'] === 'dir' ? 'deleteDir' : 'delete';
            $this->filesystem->{$method}($content['path']);
        }
    }

    /**
     * @param string $token
     *
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function read($token): ?Profile
    {
        $filename = $this->getFilename($token);

        if (empty($token) || $this->filesystem->has($filename) === false) {
            return null;
        }

        return $this->createProfileFromContents($token, (string)$this->filesystem->read($filename));
    }

    /**
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function write(Profile $profile): bool
    {
        $filename = $this->getFilename($profile->getToken());
        $profileIndexed = $this->filesystem->has($filename);
        $profileToken = $profile->getToken();

        // when there are errors in sub-requests, the parent and/or children tokens
        // may equal the profile token, resulting in infinite loops
        $parentToken = $profile->getParentToken() !== $profileToken ? $profile->getParentToken() : null;
        $childrenToken = \array_filter(\array_map(function (Profile $p) use ($profileToken) {
            return $profileToken !== $p->getToken() ? $p->getToken() : null;
        }, $profile->getChildren()));

        // Store profile
        $data = [
            'token' => $profileToken,
            'parent' => $parentToken,
            'children' => $childrenToken,
            'data' => $profile->getCollectors(),
            'ip' => $profile->getIp(),
            'method' => $profile->getMethod(),
            'url' => $profile->getUrl(),
            'time' => $profile->getTime(),
            'status_code' => $profile->getStatusCode(),
        ];

        $contents = \serialize($data);
        if (\function_exists('gzencode')) {
            $contents = \gzencode($contents, 3);
        }

        if ($this->filesystem->put($filename, (string)$contents) === false) {
            return false;
        }

        if ($profileIndexed === false) {
            $originalContents = '';

            if ($this->filesystem->has(self::INDEX_FILENAME)) {
                $originalContents = (string)$this->filesystem->read(self::INDEX_FILENAME);
            }

            $profileData = [
                $profile->getToken(),
                $profile->getIp(),
                $profile->getMethod(),
                $profile->getUrl(),
                $profile->getTime(),
                $profile->getParentToken(),
                $profile->getStatusCode(),
            ];

            $this->filesystem->put(self::INDEX_FILENAME, $originalContents . \implode(',', $profileData) . \PHP_EOL);
        }

        return true;
    }

    /**
     * @throws \League\Flysystem\FileNotFoundException
     */
    private function createProfileFromContents(string $token, string $contents, ?Profile $parent = null): Profile
    {
        // Decode contents if enabled
        if (\function_exists('gzdecode')) {
            $contents = \gzdecode($contents);
        }

        $data = \unserialize((string)$contents);

        $profile = new Profile($token);
        $profile->setIp($data['ip']);
        $profile->setMethod($data['method']);
        $profile->setUrl($data['url']);
        $profile->setTime($data['time']);
        $profile->setStatusCode($data['status_code']);
        $profile->setCollectors($data['data']);

        if ($parent === null && isset($data['parent'])) {
            $parent = $this->read($data['parent']);
        }

        if ($parent) {
            $profile->setParent($parent);
        }

        foreach ($data['children'] ?? [] as $token) {
            $filename = $this->getFilename($token);

            if (empty($token) || $this->filesystem->has($filename) === false) {
                continue;
            }

            $profile->addChild($this->createProfileFromContents(
                $token,
                (string)$this->filesystem->read($filename),
                $profile
            ));
        }

        return $profile;
    }

    /**
     * Gets filename to store data, associated to the token.
     *
     * @return string The profile filename
     */
    private function getFilename(string $token): string
    {
        // Uses 4 last characters, because first are mostly the same.
        return \sprintf('%s/%s/%s', \substr($token, -2, 2), \substr($token, -4, 2), $token);
    }
}
