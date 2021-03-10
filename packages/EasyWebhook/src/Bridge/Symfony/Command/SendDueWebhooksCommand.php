<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony\Command;

use Carbon\Carbon;
use EonX\EasyPagination\Data\StartSizeData;
use EonX\EasyWebhook\Exceptions\InvalidDateTimeException;
use EonX\EasyWebhook\Interfaces\Stores\SendAfterStoreInterface;
use EonX\EasyWebhook\Interfaces\Stores\StoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SendDueWebhooksCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'easy-webhooks:send-due-webhooks';

    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookClientInterface
     */
    private $client;

    /**
     * @var \EonX\EasyWebhook\Interfaces\Stores\StoreInterface
     */
    private $store;

    public function __construct(WebhookClientInterface $client, StoreInterface $store)
    {
        $this->client = $client;
        $this->store = $store;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('bulk', null, InputOption::VALUE_OPTIONAL, 'How many webhooks to process at once', 15)
            ->addOption(
                'sendAfter',
                null,
                InputOption::VALUE_OPTIONAL,
                'DateTime to start fetching due webhooks from, in "%s" format'
            )
            ->addOption('timezone', null, InputOption::VALUE_OPTIONAL, 'The timezone of sendAfter DateTimes')
            ->setDescription('Send "sendAfter" webhooks which are due');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        if ($this->store instanceof SendAfterStoreInterface === false) {
            $style->error(\sprintf(
                'Store "%s" does not implement "%s", cannot proceed.',
                \get_class($this->store),
                SendAfterStoreInterface::class
            ));

            return 1;
        }

        $sendAfter = null;
        $page = 1;
        $perPage = (int)$input->getOption('bulk');
        $sendAfterString = $input->getOption('sendAfter') ? (string)$input->getOption('sendAfter') : null;
        $timezone = $input->getOption('timezone') ? (string)$input->getOption('timezone') : null;

        if ($sendAfterString !== null) {
            $sendAfter = Carbon::createFromFormat(StoreInterface::DATETIME_FORMAT, $sendAfterString);

            if ($sendAfter instanceof Carbon === false) {
                throw new InvalidDateTimeException(\sprintf('Invalid DateTime provided, "%s"', $sendAfterString));
            }
        }

        do {
            $dueWebhooks = $this->store->findDueWebhooks(new StartSizeData($page, $perPage), $sendAfter, $timezone);

            foreach ($dueWebhooks->getItems() as $webhook) {
                $this->client->sendWebhook($webhook);
            }

            $page++;
        } while ($dueWebhooks->hasNextPage());

        $style->success(\sprintf('Sent %d due webhooks', $dueWebhooks->getTotalItems()));

        return 0;
    }
}
