#!/usr/bin/env bash

function _test-laravel-version() {
  (
    cd integration-tests/$1
    composer install && ./vendor/bin/phpunit tests
  )
}

if [[ $(php -i | grep "PHP Version => 7") ]]; then
  echo "Testing Laravel 7.x..."
  _test-laravel-version "7.x"
else
  echo "Skipping Laravel 7.x tests -- PHP version too high"
fi

if [[ $(php -i | grep "PHP Version => 8") ]]; then
  echo "Testing Laravel 8.x..."
  _test-laravel-version "8.x"

  echo "Testing Laravel 9.x..."
  _test-laravel-version "9.x"
else
  echo "Skipping Laravel 8.x and 9.x tests -- PHP version too low"
fi



