# Local verification (PHP 8.3–8.5)

Use this checklist before publishing PHP 8.5-related fixes for the `3.1` line. Do **not** commit credentials, personal paths, or local `phpunit.xml` overrides.

## 1. Dependencies

```bash
composer install
composer update -W
```

This branch expects **PhpSpreadsheet 5.x** (`composer.json` requirement). Resolve failures locally before opening a PR.

## 2. PHPUnit (required)

The test harness configures the `testing` connection as **MySQL** (`tests/TestCase.php`). Supply credentials only via the environment when invoking PHPUnit:

```bash
DB_HOST=127.0.0.1 \
DB_PORT=3306 \
DB_DATABASE=<your_test_database> \
DB_USERNAME=<your_test_user> \
DB_PASSWORD=<your_test_password> \
./vendor/bin/phpunit
```

Use a disposable database reserved for automated tests.

### Optional: MySQL in Docker

```bash
docker rm -f laravel-excel-test-mysql 2>/dev/null
docker run -d --name laravel-excel-test-mysql \
  -e MYSQL_ROOT_PASSWORD=<choose_a_strong_password> \
  -e MYSQL_DATABASE=<your_test_database> \
  -p 3306:3306 \
  mysql:8
```

Match `DB_*` variables to the container.

## 3. Optional static compatibility scans

PHPCompatibility is **not** bundled as a package dependency. Install it temporarily when you want PHP-version-focused static analysis:

```bash
composer config allow-plugins.dealerdirect/phpcodesniffer-composer-installer true
composer require --dev squizlabs/php_codesniffer:^4.0 phpcompatibility/php-compatibility:^10.0@alpha -W
```

Scan `src/` and `tests/` while skipping Blade fixtures:

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

Tear down when finished:

```bash
composer remove --dev squizlabs/php_codesniffer phpcompatibility/php-compatibility
```

## 4. Release-ready confirmation

- PHPUnit passes on **PHP 8.5** for the full package suite (same gate used for this compatibility pass).
- Optional: PHPCompatibility reports **no errors** for `testVersion` **8.3**, **8.4**, and **8.5**.
