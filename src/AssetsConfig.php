<?php
declare(strict_types=1);

namespace ArthuSantiago\BootstrapForCakePHP;

/**
 * Configuration for Bootstrap assets
 *
 * Defines which packages should be processed and where their files should be copied.
 * This configuration can be extended in your CakePHP application to customize paths.
 *
 * @example
 * ```php
 * class MyAssetsConfig extends AssetsConfig
 * {
 *     protected static string $webrootPath = '/custom/webroot';
 * }
 * ```
 */
class AssetsConfig
{
    /**
     * Base webroot path (relative to project root)
     * Override this in a subclass to customize
     */
    protected static string $webrootPath = 'webroot';

    /**
     * Supported packages and their asset definitions
     *
     * @var array<string, array{source: string, files: array<string>, destination: string}>
     */
    protected static array $packages = [
        'twbs/bootstrap' => [
            'source' => 'vendor/twbs/bootstrap/dist/',
            'assets' => [
                'css' => [
                    'files' => ['bootstrap.min.css', 'bootstrap.min.css.map'],
                    'destination' => 'css',
                ],
                'js' => [
                    'files' => ['bootstrap.min.js', 'bootstrap.min.js.map'],
                    'destination' => 'js',
                ],
            ],
        ],
        'twbs/bootstrap-icons' => [
            'source' => 'vendor/twbs/bootstrap-icons/font/',
            'assets' => [
                'css' => [
                    'files' => ['bootstrap-icons.min.css'],
                    'destination' => 'css',
                ],
                'fonts' => [
                    'files' => ['bootstrap-icons.woff', 'bootstrap-icons.woff2'],
                    'source' => 'vendor/twbs/bootstrap-icons/font/fonts/',
                    'destination' => 'css/fonts',
                ],
            ],
        ],
        'popperjs/core' => [
            'source' => 'vendor/popperjs/core/dist/umd/',
            'assets' => [
                'js' => [
                    'files' => ['popper.min.js'],
                    'destination' => 'js',
                ],
            ],
        ],
    ];

    /**
     * Get webroot base path
     */
    public static function getWebrootPath(): string
    {
        return static::$webrootPath;
    }

    /**
     * Get all supported packages
     *
     * @return array<string, array>
     */
    public static function getPackages(): array
    {
        return static::$packages;
    }

    /**
     * Get configuration for a specific package
     *
     * @param string $packageName
     * @return array|null
     */
    public static function getPackageConfig(string $packageName): ?array
    {
        return static::$packages[$packageName] ?? null;
    }

    /**
     * Check if a package is supported
     *
     * @param string $packageName
     * @return bool
     */
    public static function isSupportedPackage(string $packageName): bool
    {
        return isset(static::$packages[$packageName]);
    }

    /**
     * Get supported package names
     *
     * @return array<string>
     */
    public static function getSupportedPackages(): array
    {
        return array_keys(static::$packages);
    }
}
