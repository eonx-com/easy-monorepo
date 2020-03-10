<div align="center">
    <h1>EonX - EasySchedule</h1>
    <p>Provides the Command Scheduling logic of Laravel in a Symfony Console application.</p>
</div>

---

## Installation In Symfony

```bash
$ composer require eonx-com/easy-schedule
```

Until a recipe is created for this bundle you will need to register it manually:

```php
// config/bundles.php

return [
    // Other bundles...
    
    EonX\EasySchedule\Bridge\Symfony\ScheduleBundle::class => ['all' => true],
];
```

## Usage In Symfony

### Register Your Scheduled Commands

To register the scheduled commands this bundle implements a concept of "schedule providers", thanks to Symfony's
autoconfigure feature, the only thing required is to create services that implement `EonX\EasySchedule\Interfaces\ScheduleProviderInterface`.
The `ScheduleInterface` passed to the `schedule` method offers all the features of the [Laravel Console Scheduling][1].

```php
// src/Schedule/MyScheduleProvider.php

use EonX\EasySchedule\Interfaces\ScheduleInterface;
use EonX\EasySchedule\Interfaces\ScheduleProviderInterface;

final class MyScheduleProvider implements ScheduleProviderInterface
{
    public function schedule(ScheduleInterface $schedule): void
    {
        $schedule
            ->command('poc:hello-world', ['-v'])
            ->everyMinute()
            ->setMaxLockTime(120);
    
        $schedule
            ->command('poc:hello-world-2')
            ->everyFiveMinutes();
    }
}
```

### Run The Schedule

This bundle providers a console command to run the schedule:

```bash
$ php bin/console schedule:run
```

[1]: https://laravel.com/docs/5.8/scheduling
