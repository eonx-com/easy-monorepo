#!/usr/bin/env sh

# get env vars
if [ ! -z ${SSM_PATH+x} ]; then
  eval $(awsenv)
fi

ARTISAN=$(/usr/bin/find /var/www -name artisan)

# Migrate database if artisan exists
if [ -f ${ARTISAN} ]; then
    /usr/local/bin/php ${ARTISAN} doctrine:schema:update --force || exit 1
    exit 0
fi

exit 1