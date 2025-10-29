# Bootstrap for CakePHP

[![Packagist](https://img.shields.io/packagist/v/arthusantiago/bootstrap-for-cakephp.svg)](https://packagist.org/packages/arthusantiago/bootstrap-for-cakephp)
[![PHP Version](https://img.shields.io/packagist/php-v/arthusantiago/bootstrap-for-cakephp)](https://packagist.org/packages/arthusantiago/bootstrap-for-cakephp)
[![License](https://img.shields.io/packagist/l/arthusantiago/bootstrap-for-cakephp.svg)](LICENSE)

Automatically copy [Bootstrap](https://getbootstrap.com/), [Bootstrap Icons](https://icons.getbootstrap.com/), and [Popperjs](https://popper.js.org/) assets to your CakePHP `webroot/` directory when packages are installed or updated via Composer.

## Features

- ✨ Automatic copying of Bootstrap CSS and JS files
- ✨ Automatic copying of Bootstrap Icons CSS and fonts
- ✨ Automatic copying of Popperjs library
- 🚀 Works with Composer package install/update hooks
- 🛠️ Manual CLI command for on-demand copying
- ✅ Fully compatible with CakePHP 4.x and 5.x
- 🔧 Customizable configuration and paths
- 📝 Comprehensive logging via Composer IO interface

## Installation

Install the package via Composer:

```bash
composer require arthusantiago/bootstrap-for-cakephp
```

Then ensure you have Bootstrap and its dependencies installed:

```bash
composer require twbs/bootstrap twbs/bootstrap-icons popperjs/core
```

## How It Works

This package automatically detects when Bootstrap, Bootstrap Icons, or Popperjs are installed or updated via Composer, and copies the necessary files to your CakePHP `webroot/` directory:

- **Bootstrap CSS/JS** → `webroot/css/` and `webroot/js/`
- **Bootstrap Icons** → `webroot/css/` and `webroot/css/fonts/`
- **Popperjs** → `webroot/js/`

## Usage

### Automatic (Recommended)

Once installed, the package automatically copies assets whenever you:

```bash
composer install
composer update
composer require twbs/bootstrap
composer update twbs/bootstrap
```

### Manual Copy

If you need to manually copy assets, use the CLI command:

```bash
# Copy all supported packages automatically
composer copy-bootstrap-assets

# Or copy specific package assets
composer copy-bootstrap-assets twbs/bootstrap twbs/bootstrap-icons popperjs/core

# Or copy each one individually
composer copy-bootstrap-assets twbs/bootstrap
composer copy-bootstrap-assets twbs/bootstrap-icons
composer copy-bootstrap-assets popperjs/core
```

## Advanced Usage

### Customizing Asset Paths

Extend the `AssetsConfig` class to customize where assets are copied:

```php
<?php
// config/AssetsConfig.php

namespace App\Config;

use ArthuSantiago\BootstrapForCakePHP\AssetsConfig;

class MyAssetsConfig extends AssetsConfig
{
    protected static string $webrootPath = 'custom_webroot';

    // Override package configuration
    protected static array $packages = [
        // ... custom configuration
    ];
}
```

Then register your custom config in `composer.json`:

```json
{
  "scripts": {
    "post-install-cmd": [
      "ArthuSantiago\\BootstrapForCakePHP\\BootstrapAssets::setupAssets"
    ]
  }
}
```

### Custom Implementations

Extend `BootstrapAssets` to implement custom logic:

```php
<?php
namespace App\Tools;

use ArthuSantiago\BootstrapForCakePHP\BootstrapAssets;
use Composer\Script\Event;

class CustomBootstrapAssets extends BootstrapAssets
{
    public static function setupAssets(Event $event): void
    {
        parent::setupAssets($event);

        // Add custom logic here
        // e.g., compile SCSS, minify files, etc.
    }
}
```

### Running Tests

First, install development dependencies:

```bash
composer install
```

Then run the test suite:

```bash
# Run all tests
composer test

# Or run directly with PHPUnit
vendor/bin/phpunit

# List all available tests
vendor/bin/phpunit --list-tests

# Run specific test file
vendor/bin/phpunit tests/TestCase/FileOperationsTest.php

# Run specific test method
vendor/bin/phpunit --filter testCopyFileShouldCopyFileSuccessfully

# Run tests from specific testsuite
vendor/bin/phpunit --testsuite "Bootstrap Assets Test Suite"
```

## Requirements

- PHP 8.1 or higher
- CakePHP 4.0 or higher
- Composer 2.0 or higher

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit issues and pull requests to improve this package.

## Support

If you encounter any issues, please open an issue on the [GitHub repository](https://github.com/arthusantiago/bootstrap-for-cakephp/issues).
