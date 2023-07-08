#!/usr/bin/env bash

function _test-laravel-version() {
  (
    cd integration-tests/$1
    composer update && ./vendor/bin/phpunit tests
  )
}

echo $(php -i | grep "PHP Version => 8.1.*")

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

if [[ $(php -i | grep "PHP Version => 8.1.*") || $(php -i | grep "PHP Version => 8.2.*") ]]; then
  echo "Testing Laravel 10.x..."
  _test-laravel-version "10.x"
else
  echo "Skipping Laravel 10.x tests -- PHP version too low"
fi


