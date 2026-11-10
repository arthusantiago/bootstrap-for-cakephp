<?php
declare(strict_types=1);

namespace ArthuSantiago\BootstrapForCakePHP\Test\TestCase;

use ArthuSantiago\BootstrapForCakePHP\AssetsConfig;
use ArthuSantiago\BootstrapForCakePHP\BootstrapAssets;
use Composer\IO\NullIO;
use PHPUnit\Framework\TestCase;

/**
 * Config pointing to a temporary webroot and a fake package.
 */
class TemporaryAssetsConfig extends AssetsConfig
{
    public static string $root = '';

    public static function getWebrootPath(): string
    {
        return self::$root . DIRECTORY_SEPARATOR . 'webroot';
    }

    public static function getPackages(): array
    {
        return [
            'vendor/fake-icons' => [
                'source' => self::$root . '/vendor/fake-icons/',
                'assets' => [
                    'fonts' => [
                        'files' => ['icons.woff', 'icons.woff2'],
                        'source' => self::$root . '/vendor/fake-icons/fonts/',
                        'destination' => 'fonts/fake-icons',
                        'filePermission' => 0750,
                    ],
                ],
            ],
        ];
    }

    public static function getPackageConfig(string $packageName): ?array
    {
        return static::getPackages()[$packageName] ?? null;
    }
}

class BootstrapAssetsTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/bootstrap-assets-test-' . uniqid();
        mkdir($this->tmpDir . '/vendor/fake-icons/fonts', 0755, true);
        file_put_contents($this->tmpDir . '/vendor/fake-icons/fonts/icons.woff', 'woff');
        file_put_contents($this->tmpDir . '/vendor/fake-icons/fonts/icons.woff2', 'woff2');

        TemporaryAssetsConfig::$root = $this->tmpDir;
        BootstrapAssets::setConfigClass(TemporaryAssetsConfig::class);
        BootstrapAssets::setIO(new NullIO());
    }

    protected function tearDown(): void
    {
        BootstrapAssets::setConfigClass(AssetsConfig::class);
        $this->removeDirectory($this->tmpDir);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }

    /**
     * First install: the nested destination (webroot/fonts/fake-icons) doesn't exist yet. The files
     * must end up there, and no temporary directory may be left behind.
     */
    public function testProcessPackageCreatesNestedDestinationOnFirstInstall(): void
    {
        $destination = $this->tmpDir . '/webroot/fonts/fake-icons';
        $this->assertDirectoryDoesNotExist($destination);

        BootstrapAssets::processPackage('vendor/fake-icons');

        $this->assertFileExists($destination . '/icons.woff');
        $this->assertFileExists($destination . '/icons.woff2');
        $this->assertSame('woff2', file_get_contents($destination . '/icons.woff2'));
        $this->assertSame([], glob($this->tmpDir . '/webroot/fonts/fake-icons.tmp-*') ?: []);
    }

    /**
     * Update: the destination already exists with old files, which are replaced.
     */
    public function testProcessPackageReplacesExistingFiles(): void
    {
        $destination = $this->tmpDir . '/webroot/fonts/fake-icons';
        mkdir($destination, 0755, true);
        file_put_contents($destination . '/icons.woff', 'old');

        BootstrapAssets::processPackage('vendor/fake-icons');

        $this->assertSame('woff', file_get_contents($destination . '/icons.woff'));
        $this->assertFileExists($destination . '/icons.woff2');
    }
}
