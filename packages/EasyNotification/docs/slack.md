---eonx_docs---
title: Slack Integration
weight: 3
is_section: true
section_icon: fab fa-slack
---eonx_docs---

In order to send [Slack][1] messages you will need to create an App in your Workspace, make sure you create a Bot user
for this app and request the `chat:write`, and `chat:write:public` scopes for its Bot Token. Then add this Bot Token
to your Provider in the EonX Notification service.

That's it! You're all setup to start sending Slack messages within your Workspace!

<br>

### How To Send Slack Messages

Sending Slack messages requires installing 3 additional packages, using a python script and certificate signed by
[Stewart Butterfield][2] himself!

Just kidding... Simply use your existing `EonX\EasyNotification\Client\NotificationClientInterface` service!

<br>

### Simple Slack Message

The simplest Slack message is made of a `channel` and a `text`.

```php
// src/Listener/UserCreatedListener.php

namespace App\Listener;

use App\Entity\User;use EonX\EasyNotification\Client\NotificationClientInterface;use EonX\EasyNotification\Message\SlackMessage;use EonX\EasyNotification\Provider\ConfigProviderInterface;

final class UserCreatedListener
{
    /**
     * @var \EonX\EasyNotification\Provider\ConfigProviderInterface
     */
    private $configFinder;

    /**
     * @var \EonX\EasyNotification\Client\NotificationClientInterface
     */
    private $client;

    public function __construct(ConfigProviderInterface $configFinder, NotificationClientInterface $client)
    {
        $this->configFinder = $configFinder;
        $this->client = $client;
    }

    public function created(User $user): void
    {
        $config = $this->configFinder->find('my-api-key', 'my-provider-external-id');

        /**
         * Define channel. It can be a direct message to a user "@user" or a public channel "#channel".
         */
        $channel = \sprintf('@%s.%s', $user->getFirstName(), $user->getLastName());

        /**
         * Simple text.
         */
        $text = \sprintf('We are to have you onboard %s', $user->getUsername());

        $this->client
             ->withConfig($config) // Set config for next send
             ->send(SlackMessage::create($channel, $text)); // Send Slack time message
    }
}
```

<br>

### Advanced Formatted Slack Message

Slack offers a great panel of advanced formatting when sending messages. Checkout the [Block Kit Builder][3] to see
what you can do for your messages.

The `SlackMessage` object allows you to pass the formatting options as an associative array to define the `body` of the
message. This package doesn't come with helpers to build these advanced formatted messages as other packages are already
doing it very well such as:

- [Symfony Slack Notifier][5]
- [Maknz Slack][4]

```php
$slackOptions = [
    "blocks" => [
        [
            "type" =>"section",
            "text" => [
                "type" => "mrkdwn",
                "text" => "This is a section block with a button.",
            ],
            "accessory" => [
                "type" => "button",
                "text" => [
                    "type" => "plain_text",
                	"text" => "Click Me",
                	"emoji" => true,
                ],
                "value" => "click_me_123",
            ],
        ],
    ],
];

$slackMessage = SlackMessage::create('@channel', 'Simple text', $slackOptions);
```

[1]: https://slack.com/

[2]: https://en.wikipedia.org/wiki/Stewart_Butterfield

[3]: https://app.slack.com/block-kit-builder

[4]: https://github.com/maknz/slack

[5]: https://github.com/symfony/slack-notifier
