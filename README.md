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
- 📦 Zero dependencies
- ✅ Fully compatible with CakePHP 4.x and 5.x
- 🔧 Customizable configuration and paths
- ⚡ Proper error handling with typed exceptions
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

## Error Handling

The package includes proper exception handling:

- **`BootstrapAssetsException`** - Base exception for all asset operations
- **`UnsupportedPackageException`** - Thrown when trying to process unsupported packages
- **`FileOperationException`** - Thrown when file copy/delete operations fail

```php
use ArthuSantiago\BootstrapForCakePHP\Exception\FileOperationException;
use ArthuSantiago\BootstrapForCakePHP\Exception\UnsupportedPackageException;

try {
    BootstrapAssets::processPackage('twbs/bootstrap');
} catch (UnsupportedPackageException $e) {
    // Handle unsupported package
} catch (FileOperationException $e) {
    // Handle file operation error
}
```

## Testing

Run the test suite:

```bash
composer test
```

The package includes comprehensive unit tests for:
- File operations (copy, delete)
- Configuration management
- Path utilities
- Exception handling

## API Reference

### BootstrapAssets

#### Static Methods

**`processPackage(string $packageName): void`**
- Process a specific package and copy its assets
- Throws `UnsupportedPackageException` if package is not supported

**`setIO(IOInterface $io): void`**
- Set the Composer IO interface for logging

**`setConfigClass(string $configClass): void`**
- Set custom configuration class

### AssetsConfig

#### Static Methods

**`getWebrootPath(): string`**
- Get the base webroot path

**`getPackages(): array`**
- Get all supported packages and their configurations

**`getPackageConfig(string $packageName): ?array`**
- Get configuration for a specific package

**`isSupportedPackage(string $packageName): bool`**
- Check if a package is supported

**`getSupportedPackages(): array`**
- Get list of all supported package names

### FileOperations

#### Static Methods

**`copyFile(string $source, string $destination): bool`**
- Copy a single file with automatic directory creation

**`copyMultipleFiles(string $sourceDir, string $destinationDir, array $files): array`**
- Copy multiple files at once

**`deleteFile(string $file): bool`**
- Delete a file safely

**`deleteMultipleFiles(string $directory, array $files): array`**
- Delete multiple files at once

**`normalizePath(string $path, bool $trailingSlash = true): string`**
- Normalize path with proper separators

**`joinPaths(string ...$segments): string`**
- Join multiple path segments safely

## Requirements

- PHP 8.1 or higher
- CakePHP 4.0 or higher
- Composer 2.0 or higher

## Changelog

### v2.0.0 (Improved Release)
- Refactored with English naming conventions
- Added `AssetsConfig` for external configuration
- Added `FileOperations` utility class
- Implemented proper exception handling
- Added Composer IO interface for logging
- Added comprehensive unit tests
- Improved documentation

### v1.0.0 (Initial Release)
- Initial release with support for Bootstrap, Bootstrap Icons, and Popperjs

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit issues and pull requests to improve this package.

## Support

If you encounter any issues, please open an issue on the [GitHub repository](https://github.com/arthusantiago/bootstrap-for-cakephp/issues).
