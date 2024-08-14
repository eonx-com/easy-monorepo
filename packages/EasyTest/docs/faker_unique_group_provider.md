---eonx_docs---
title: UniqueGroupProvider for Faker
weight: 1001
---eonx_docs---

# UniqueGroupProvider for Faker

This provider is useful for creating entities where some property or combination of properties must be unique among all entities in the table.

By creating multiple entities without this provider and without setting unique properties (combinations of properties) manually for each entity, there is a risk that due to random generation of values, the next entity will receive the same value (or
combination of values) as one of the already created entities, which will result in a unique constraint violation.

With the unique group generator you are guaranteed to never get the same combination of values within the same group in tests or fixtures.

### Example:

The `ReferralCode` entity has the `code` and `status` fields. The status can be `active` or `inactive`. There can be 2 identical referral codes in the table, but only with different statuses (only one can be `active`). Thus, we need a unique
constraint on columns (`code`, `status`).

In the `ReferralCodeFactory` we can use `UniqueGroupProvider` like this:

```php
protected function getDefaults(): array
{
    return [
        'code' => self::faker()
            ->uniqueGroup('referral-code-unique-group')
            ->word(),
        'status' => self::faker()
            ->uniqueGroup('referral-code-unique-group')
            ->randomElement(ReferralCode::ALL_STATUSES),
    ];
}
```

Please note that we use the same group name for both fields. It means that the combination of `code` and `status` fields will be unique among this group.

### Usage notes

Don't forget to add `UniqueGroupProvider` to the Faker's provider collection so that it can be used:

```php
$faker->addProvider(new UniqueGroupProvider($faker));
```

Also, you should clear unique groups after each test using `UniqueGroupProvider::clearUniqueGroups` method, e.g in `tearDown` method of your test case:

```php
foreach (AbstractFactory::faker()->getProviders() as $provider) {
    if ($provider instanceof UniqueGroupProvider) {
        $provider->clearUniqueGroups();

        break;
    }
}
```
