<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Command;

use Carbon\Carbon;
use EonX\EasyPagination\Pagination\Pagination;
use EonX\EasyWebhook\Common\Client\WebhookClientInterface;
use EonX\EasyWebhook\Common\Exception\InvalidDateTimeException;
use EonX\EasyWebhook\Common\Store\SendAfterStoreInterface;
use EonX\EasyWebhook\Common\Store\StoreInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'easy-webhooks:send-due-webhooks',
    description: 'Sends "sendAfter" webhooks which are due'
)]
final class SendDueWebhooksCommand extends Command
{
    public function __construct(
        private readonly WebhookClientInterface $client,
        private readonly StoreInterface $store,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('bulk', null, InputOption::VALUE_OPTIONAL, 'How many webhooks to process at once', '15')
            ->addOption(
                'sendAfter',
                null,
                InputOption::VALUE_OPTIONAL,
                'DateTime to start fetching due webhooks from, in "%s" format'
            )
            ->addOption('timezone', null, InputOption::VALUE_OPTIONAL, 'The timezone of sendAfter DateTimes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        if ($this->store instanceof SendAfterStoreInterface === false) {
            $style->error(\sprintf(
                'Store "%s" does not implement "%s", cannot proceed.',
                $this->store::class,
                SendAfterStoreInterface::class
            ));

            return 1;
        }

        $sendAfter = null;
        $page = 1;
        /** @var string|false $bulkValue */
        $bulkValue = $input->getOption('bulk');
        $perPage = (int)$bulkValue;
        /** @var string|false $sendAfterValue */
        $sendAfterValue = $input->getOption('sendAfter');
        $sendAfterString = $sendAfterValue !== false ? $sendAfterValue : null;
        /** @var string|false $timezoneValue */
        $timezoneValue = $input->getOption('timezone');
        $timezone = $timezoneValue !== false ? $timezoneValue : null;
        $webhooksSent = 0;

        if ($sendAfterString !== null) {
            $sendAfter = Carbon::createFromFormat(StoreInterface::DATETIME_FORMAT, $sendAfterString, $timezone);

            if ($sendAfter instanceof Carbon === false) {
                throw new InvalidDateTimeException(\sprintf('Invalid DateTime provided, "%s"', $sendAfterString));
            }
        }

        do {
            $dueWebhooks = $this->store->findDueWebhooks(new Pagination($page, $perPage), $sendAfter, $timezone);

            foreach ($dueWebhooks->getItems() as $webhook) {
                $this->client->sendWebhook($webhook);

                $webhooksSent++;
            }

            $page++;
        } while ($dueWebhooks->hasNextPage());

        $style->success(\sprintf('Sent %d due webhooks', $webhooksSent));

        return 0;
    }
}
