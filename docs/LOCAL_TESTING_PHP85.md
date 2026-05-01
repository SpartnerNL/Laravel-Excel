# Local verification (PHP 8.3–8.5)

Use this checklist before pushing changes that affect PHP or PhpSpreadsheet compatibility. Do **not** commit credentials or machine-specific paths.

## 1. Dependencies

```bash
composer install
composer update -W
```

## 2. PHPUnit (required)

The test harness configures the `testing` connection as **MySQL** (`tests/TestCase.php`). Pass credentials via environment variables at invoke time (or via `phpunit.xml` locally, which must stay out of commits).

```bash
DB_HOST=127.0.0.1 \
DB_PORT=3306 \
DB_DATABASE=<your_test_database> \
DB_USERNAME=<your_test_user> \
DB_PASSWORD=<your_test_password> \
./vendor/bin/phpunit
```

Tip: use a dedicated database whose name clearly indicates it is for testing.

### Optional: MySQL in Docker

```bash
docker rm -f laravel-excel-test-mysql 2>/dev/null
docker run -d --name laravel-excel-test-mysql \
  -e MYSQL_ROOT_PASSWORD=<choose_a_strong_password> \
  -e MYSQL_DATABASE=<your_test_database> \
  -p 3306:3306 \
  mysql:8
```

Then run PHPUnit with matching `DB_*` variables.

## 3. Optional static compatibility scans

PHPCompatibility is **not** a runtime dependency of this package. To scan `src/` and `tests/` against PHP 8.3, 8.4, or 8.5, install the tooling temporarily in your clone:

```bash
composer config allow-plugins.dealerdirect/phpcodesniffer-composer-installer true
composer require --dev squizlabs/php_codesniffer:^4.0 phpcompatibility/php-compatibility:^10.0@alpha -W
```

Example scans (exclude Blade stubs from PHP-only analysis):

```bash
./vendor/bin/phpcs -ps --standard=PHPCompatibility src tests \
  --ignore='*.blade.php' \
  --runtime-set testVersion 8.3

./vendor/bin/phpcs -ps --standard=PHPCompatibility src tests \
  --ignore='*.blade.php' \
  --runtime-set testVersion 8.4

./vendor/bin/phpcs -ps --standard=PHPCompatibility src tests \
  --ignore='*.blade.php' \
  --runtime-set testVersion 8.5
```

Remove the dev tools when finished if you prefer a minimal tree:

```bash
composer remove --dev squizlabs/php_codesniffer phpcompatibility/php-compatibility
```

## 4. Release-ready confirmation

- PHPUnit passes on your target PHP (PHP **8.5** was used for the compatibility pass behind this branch).
- Optional: PHPCompatibility reports **no errors** for `testVersion` **8.3**, **8.4**, and **8.5** using the commands above.
