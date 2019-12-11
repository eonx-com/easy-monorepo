# To enable configuration cache

- Register `EonX\EasyCore\Bridge\Laravel\CachedConfigurationServiceProvider` at `bootstrap/app.php`
  instead of `ConfigurationServiceProvider`
- Execute `php artisan config:cache` at container start or whenever you want to update cache
- Add `echo "config:cache"` and `/usr/local/bin/php ${ARTISAN} config:cache` lines to startup.sh

# To clear configuration cache

- Execute `php artisan config:clear`

# To disable configuration cache

- Remove `echo "config:cache"` and `/usr/local/bin/php ${ARTISAN} config:cache` lines from startup.sh
- Clear cache by executing `php artisan config:clear`
- (Optionally) Replace `CachedConfigurationServiceProvider` with `ConfigurationServiceProvider` at `bootstrap/app.php`
