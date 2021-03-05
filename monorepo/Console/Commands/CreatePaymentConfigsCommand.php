<?php

declare(strict_types=1);

namespace EonX\EasyMonorepo\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

final class CreatePaymentConfigsCommand extends Command
{
    protected static $defaultName = 'payments:create-configs';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $handle = \fopen(__DIR__ . '/curl-commands.txt', 'r');

        while(($line = \fgets($handle)) !== false) {
            $token = $this->getToken();
            $command = \str_replace('<bearer_token>', $token, $line);

            $style->section($command);

            $process = Process::fromShellCommandline($command);
            $process->run(static function ($status, $buffer) use ($output): void {
                $output->writeln($buffer);
            });
        }

        return 0;
    }

    private function getToken(): string
    {
        return 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6IlJqRXhNVEZGTjBKRU5UUTFOVVl3TmpRMU1UTTVNak5EUmpsRlJrWkVSRVZGUTBReVFqYzVOQSJ9.eyJodHRwczovL21hbmFnZS5lb254LmNvbS9naXZlbl9uYW1lIjoiTmF0aGFuIiwiaHR0cHM6Ly9tYW5hZ2UuZW9ueC5jb20vZmFtaWx5X25hbWUiOiJQYWdlIiwiaHR0cHM6Ly9tYW5hZ2UuZW9ueC5jb20vZW1haWwiOiJuYXRoYW4ucGFnZUBlb254LmNvbSIsImh0dHBzOi8vbWFuYWdlLmVvbnguY29tL3JvbGVzIjpbInBheW1lbnRfZ2F0ZXdheTpzdXBlcl9hZG1pbiJdLCJodHRwczovL21hbmFnZS5lb254LmNvbS8iOnt9LCJodHRwczovL2VvbnguY29tL3VzZXIiOnsiaWQiOiI4YTk2NzI4NS00ZWI4LTExZWEtODQxOC0wMmZmYzIwM2VkMTIiLCJmbiI6Ik5hdGhhbiIsImxuIjoiUGFnZSIsImVtYWlsIjoibmF0aGFuLnBhZ2VAZW9ueC5jb20iLCJ2ZXJzaW9uIjoiVmVyc2lvbiAxIiwicm9sZXMiOlsicGF5bWVudF9nYXRld2F5OnN1cGVyX2FkbWluIl0sInN3aXRjaGVkVmlldyI6ZmFsc2V9LCJpc3MiOiJodHRwczovL2F1dGguZGV2LmVvbnguY29tLyIsInN1YiI6Imdvb2dsZS1hcHBzfG5hdGhhbi5wYWdlQGVvbnguY29tIiwiYXVkIjpbImNvbS5lb254LnBheW1lbnQtZ2F0ZXdheS52My5kZXYiLCJodHRwczovL2VvbngtZGV2LmF1LmF1dGgwLmNvbS91c2VyaW5mbyJdLCJpYXQiOjE2MTM1NDExOTIsImV4cCI6MTYxMzYyNzU5MiwiYXpwIjoiN2p4MWM1aGpwc0g2V2ZhNUN3eWZSVmVXV2c1NjFWTDgiLCJzY29wZSI6Im9wZW5pZCBwcm9maWxlIGVtYWlsIn0.h2G4J6JHSVDTPPCnsZk8xQoPTF4HlZbMY_d0VCGJSAfEdM6iCiTJ3kPzrRswUJYB4bPrZgSf-wQXjNXKcyGejg4hg4qtE8fxg4wYngclt91GSuPpX9u5rR56HdOpuLorVCuZ6QoRSQEhT7TTDd8fVH9BPhjpYOr-B-F9sU5S_PDWxHB5fs22KQOdRk62eKf9GCWkuTUiOEudk53BMdk7zvtn7HVNuNFwllgz54dVfa_8KN7YA3P6XAcL3cyErTw_ZuHdAEytcK_qdZkq2I5YMomaHNFuGcnte9rYQS-6wJvx1m2HbjvClBzW_M5SGwNrHxHJy4wUJbLmILEWiT9Dlg';
    }
}
