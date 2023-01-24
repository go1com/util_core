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
```shell
composer install
```

## Unit tests
- Run all tests: 
```
XDEBUG_MODE=coverage phpunit -c phpunit.xml --coverage-text
```