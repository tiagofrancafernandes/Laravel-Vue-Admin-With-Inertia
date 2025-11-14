# Laravel Dusk Setup Guide - Complete E2E Testing

**Updated**: 14 de Novembro de 2025
**Purpose**: Complete guide to set up and run Laravel Dusk E2E tests for Simple Ledger Application

---

## 📋 Overview

Laravel Dusk is a browser automation testing framework that allows you to test JavaScript interactions, form submissions, and user flows. This guide covers complete setup and usage.

---

## 🚀 System Requirements

### Operating System
- Linux (Ubuntu 20.04+, Debian, etc.)
- macOS (Homebrew)
- Windows (WSL2 recommended)

### Software Dependencies
```bash
# Core requirements
- PHP 8.3+
- Composer
- Chrome/Chromium browser
- ChromeDriver (compatible with your Chrome version)
- Node.js 16+ (for assets)
```

---

## 📦 Installation Steps

### Step 1: Install Chrome/Chromium

**Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install -y chromium-browser chromium-chromedriver

# Verify installation
google-chrome --version
chromedriver --version
```

**macOS:**
```bash
# Using Homebrew
brew install chromium-chromedriver

# Verify
chromedriver --version
```

**Windows (WSL2):**
```bash
# In WSL2 Ubuntu terminal
sudo apt-get update
sudo apt-get install -y chromium-browser chromium-chromedriver
```

### Step 2: Verify ChromeDriver is in PATH

```bash
# Should return path to chromedriver
which chromedriver

# If not found, add to PATH
export PATH=$PATH:/snap/bin  # Usually installed here via snap

# Make permanent (add to ~/.bashrc)
echo 'export PATH=$PATH:/snap/bin' >> ~/.bashrc
source ~/.bashrc
```

### Step 3: Verify Application Setup

```bash
# Navigate to project
cd /path/to/Simple-Ledger-Notebook-via-claude-code-web

# Check dependencies are installed
composer status

# Build frontend
npm run build

# Or with Yarn
yarn build
```

### Step 4: Prepare Database

```bash
# Create fresh database for testing
php artisan migrate:fresh --seed

# Verify database is ready
php artisan tinker
# Type: User::count()  // Should return 2
# Type: PaymentMethod::count()  // Should return 6
```

---

## 🧪 Running Tests

### Basic Test Execution

```bash
# Run all tests
php artisan dusk

# Run specific test class
php artisan dusk tests/Browser/AuthFlowTest.php

# Run specific test method
php artisan dusk tests/Browser/AuthFlowTest.php --filter=testSuccessfulLoginFlow
```

### Useful Options

```bash
# Verbose output
php artisan dusk --verbose

# Show debugging info
php artisan dusk --debug

# Run in parallel (faster)
php artisan dusk --parallel

# Number of parallel workers
php artisan dusk --parallel --workers=4

# Run with specific PHP ini setting
php artisan dusk --php-ini=/etc/php/8.3/cli/php.ini

# Fail on first error
php artisan dusk --stop-on-failure
```

### Example Test Run

```bash
$ php artisan dusk

   PASS  Tests\Browser\AuthFlowTest
  ✓ successful login flow                                            1.24s
  ✓ dashboard loads after login                                      1.15s
  ✓ dark mode toggle                                                 0.92s
  ✓ navigation menu                                                  1.08s
  ✓ logout functionality                                             1.05s
  ✓ navigation to sales page                                         1.12s
  ✓ navigation to clients page                                       1.10s
  ✓ unauthenticated user redirection                                 0.98s

   PASS  Tests\Browser\SalesFlowTest
  ✓ complete sales creation flow                                     2.45s
  ✓ sales creation payment validation error                          1.55s
  ✓ sales listing page loads                                         1.08s
  ✓ sales search functionality                                       1.25s
  ✓ unauthenticated user redirection                                 0.95s

   PASS  Tests\Browser\ClientsFlowTest
  ✓ complete client creation flow                                    2.12s
  ✓ client creation with cnpj validation                             1.88s
  ✓ client creation with invalid document                            1.42s
  ✓ clients listing page loads                                       1.05s
  ✓ client search functionality                                      1.32s
  ✓ client profile page                                              1.08s
  ✓ client profile displays sections                                 1.15s
  ✓ navigation back from create form                                 1.03s

   PASS  Tests\Browser\ExampleTest
  ✓ basic example                                                    0.45s

────────────────────────────────────────────────────────────────
 22 tests passed
────────────────────────────────────────────────────────────────
```

---

## 🐳 Docker Setup (Recommended)

### Using Official Docker Image

```bash
# Pull the Laravel Dusk CI image
docker pull laravel/dusk-ci:latest

# Run tests in Docker
docker run --rm \
  -v $(pwd):/app \
  laravel/dusk-ci:latest \
  php artisan dusk
```

### Docker Compose Configuration

Create `docker-compose.dusk.yml`:

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    volumes:
      - ./:/app
    working_dir: /app
    environment:
      - APP_ENV=testing
      - APP_DEBUG=true
      - DB_CONNECTION=sqlite
      - DUSK_DRIVER_URL=http://chrome:9515

  chrome:
    image: chromium:latest
    ports:
      - "9515:9515"
    command:
      - /usr/bin/chromium-browser
      - --no-sandbox
      - --headless
      - --disable-gpu
      - --remote-debugging-address=0.0.0.0
      - --remote-debugging-port=9515
    depends_on:
      - app

  chrome-debug:
    image: chromium:latest
    ports:
      - "5900:5900"
      - "9515:9515"
    volumes:
      - /dev/shm:/dev/shm
    command:
      - /usr/bin/chromium-browser
      - --no-sandbox
      - --disable-gpu
      - --start-maximized
      - --remote-debugging-address=0.0.0.0
      - --remote-debugging-port=9515
```

Run with Docker Compose:

```bash
# Start services
docker-compose -f docker-compose.dusk.yml up -d

# Run tests
docker-compose -f docker-compose.dusk.yml exec app php artisan dusk

# View Chrome VNC (for debugging)
vncviewer localhost:5900
```

---

## 🛠️ Troubleshooting

### Error: "cannot find Chrome binary"

**Solution 1**: Install Chromium
```bash
sudo apt-get install -y chromium-browser
```

**Solution 2**: Use explicit Chrome path
```bash
export CHROME_EXECUTABLE=/path/to/chrome
php artisan dusk
```

**Solution 3**: Check ChromeDriver version matches Chrome
```bash
google-chrome --version      # e.g., Google Chrome 120.0.0.0
chromedriver --version       # Should match major version
```

### Error: "Connection refused" on port 9515

**Solution**: ChromeDriver not running properly

```bash
# Kill any existing chromedriver processes
pkill -f chromedriver

# Start chromedriver manually in background
chromedriver --port=9515 &

# Run tests
php artisan dusk
```

### Tests Timeout or Hang

**Solution**: Increase timeout in `php.ini` or `.env`

```php
// phpunit.xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DUSK_DRIVER_URL" value="http://localhost:9515"/>
    <ini name="max_execution_time" value="120"/>
    <ini name="default_socket_timeout" value="120"/>
</php>
```

### Database Locked Error

**Solution**: SQLite locking issue

```bash
# Use MySQL for testing instead
# .env.testing
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=simple_ledger_test
DB_USERNAME=root
DB_PASSWORD=

# Or use in-memory database
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

### Memory Issues

**Solution**: Increase PHP memory limit

```bash
php -d memory_limit=512M artisan dusk
```

---

## 📊 CI/CD Integration

### GitHub Actions

```yaml
name: Dusk Tests

on: [push, pull_request]

jobs:
  dusk:
    runs-on: ubuntu-latest

    services:
      chrome:
        image: chromium:latest
        options: --disable-gpu --no-sandbox

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: sqlite3

      - name: Install composer dependencies
        run: composer install -q --no-ansi --no-interaction --no-scripts --no-progress

      - name: Install npm dependencies
        run: npm ci

      - name: Build assets
        run: npm run build

      - name: Generate app key
        run: php artisan key:generate --env=testing

      - name: Run migrations
        run: php artisan migrate:fresh --seed --env=testing

      - name: Execute Dusk tests
        run: php artisan dusk --parallel

      - name: Upload screenshots on failure
        uses: actions/upload-artifact@v3
        if: failure()
        with:
          name: screenshots
          path: tests/Browser/screenshots/

      - name: Upload console logs on failure
        uses: actions/upload-artifact@v3
        if: failure()
        with:
          name: logs
          path: tests/Browser/console/
```

### GitLab CI

```yaml
dusk:
  image: laravel/dusk-ci:latest
  services:
    - chromium:latest
  script:
    - composer install
    - npm ci && npm run build
    - php artisan key:generate --env=testing
    - php artisan migrate:fresh --seed --env=testing
    - php artisan dusk
  artifacts:
    when: on_failure
    paths:
      - tests/Browser/screenshots/
      - tests/Browser/console/
```

---

## 📝 Writing Tests

### Basic Test Template

```php
<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test user can login
     */
    public function testLogin(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'password')
                ->press('Log in')
                ->assertAuthenticated();
        });
    }
}
```

### Available Dusk Methods

```php
// Navigation
$browser->visit('/path')
$browser->visitRoute('route.name')
$browser->back()
$browser->forward()
$browser->refresh()

// Assertions
$browser->assertSee('text')
$browser->assertDontSee('text')
$browser->assertPathIs('/path')
$browser->assertRouteIs('route.name')
$browser->assertAuthenticated()
$browser->assertGuest()

// Form Interactions
$browser->type('email', 'test@example.com')
$browser->select('role', 'admin')
$browser->check('accept-terms')
$browser->uncheck('accept-terms')
$browser->radio('gender', 'male')
$browser->press('Submit')
$browser->attach('photo', '/path/to/file.jpg')

// Element Methods
$browser->click('selector')
$browser->assertPresent('selector')
$browser->assertMissing('selector')
$browser->assertVisible('selector')
$browser->assertHidden('selector')
$browser->getAttribute('selector', 'attribute')
$browser->getText('selector')

// Waiting
$browser->waitFor('selector')
$browser->waitForRoute('route.name')
$browser->waitUntilMissing('selector')
$browser->pause(1000)  // milliseconds

// Screenshots
$browser->screenshot('filename')
$browser->responsiveScreenshots('filename')
```

---

## ✅ Verification Checklist

Before running tests, verify:

- ✅ Chrome/Chromium installed: `google-chrome --version`
- ✅ ChromeDriver installed: `chromedriver --version`
- ✅ ChromeDriver in PATH: `which chromedriver`
- ✅ PHP 8.3+: `php --version`
- ✅ Composer dependencies: `composer check`
- ✅ Node dependencies: `npm ci`
- ✅ Frontend built: `npm run build` or `yarn build`
- ✅ Database seeded: `php artisan migrate:fresh --seed`
- ✅ Server running: `php artisan serve`

---

## 🎯 Next Steps

1. **Set up CI/CD**: Choose GitHub Actions, GitLab CI, or similar
2. **Schedule tests**: Run on commits, pull requests, nightly
3. **Monitor results**: Track test coverage and pass rates
4. **Add more tests**: Expand test coverage as features grow
5. **Performance testing**: Use Dusk with profiling tools

---

## 📚 Additional Resources

- [Laravel Dusk Documentation](https://laravel.com/docs/11.x/dusk)
- [Chromium Binary Download](https://chromedriver.chromium.org/)
- [Docker Dusk Image](https://hub.docker.com/r/laravel/dusk-ci)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)

---

**Last Updated**: 14 de Novembro de 2025
**Application**: Simple Ledger System
**Laravel Version**: 11.x
**PHP Version**: 8.3+
