Core utilities
===

The core including:

- portal
- user
- lo
- enrolment
- mail
- error handling
- controller interfaces:
    - consumer
    - cron
    - install

## Installation
```sh
composer install
```

## Unit tests
- Run all tests: 
```sh
XDEBUG_MODE=coverage phpunit -c phpunit.xml --coverage-text
```