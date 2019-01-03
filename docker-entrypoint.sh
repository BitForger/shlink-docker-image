#!/usr/bin/env sh
set -e

cd /etc/shlink

# If proxies have not been generated yet, run first-time operations
if [[ -n "$(echo "$ECS_CONTAINER_METADATA_URI")" ]]; then
    # NOTE: We are in an ECS environment so we don't need to re-init as long as the tables already exist
    doTablesExist=$(php -f ./Script.php)
    if [[ "$doTablesExist" = false ]]; then
        rm -f data/cache/app_config.php
        php vendor/doctrine/orm/bin/doctrine.php orm:schema-tool:create
        php vendor/doctrine/migrations/bin/doctrine-migrations.php migrations:migrate
        php vendor/doctrine/orm/bin/doctrine.php orm:generate-proxies
        shlink visit:update-db
    fi
elif  [ -z "$(ls -A data/proxies)" ]; then
    rm -f data/cache/app_config.php
    php vendor/doctrine/orm/bin/doctrine.php orm:schema-tool:create
    php vendor/doctrine/migrations/bin/doctrine-migrations.php migrations:migrate
    php vendor/doctrine/orm/bin/doctrine.php orm:generate-proxies
    shlink visit:update-db
fi

# When restarting the container, swoole might think it is already in execution
# This forces the app to be started every second until the exit code is 0
until php vendor/zendframework/zend-expressive-swoole/bin/zend-expressive-swoole start; do sleep 1 ; done
