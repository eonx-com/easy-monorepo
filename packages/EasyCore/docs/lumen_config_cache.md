# To enable configuration cache

- Register `LoyaltyCorp\EasyCore\Bridge\Laravel\CachedConfigurationServiceProvider` at `bootstrap/app.php`
  instead of `ConfigurationServiceProvider`
- Execute `php artisan cache:config` at container start or whenever you want to update cache
- Add `echo "cache:config"` and `/usr/local/bin/php ${ARTISAN} cache:config` lines to startup.sh

# To clear configuration cache

- Execute `php artisan cache:clear`

# To disable configuration cache

- Remove `echo "config:cache"` and `/usr/local/bin/php ${ARTISAN} config:cache` lines from startup.sh
- Clear cache by executing `php artisan cache:clear`
- (Optionally) Replace `CachedConfigurationServiceProvider` with `ConfigurationServiceProvider` at `bootstrap/app.php`