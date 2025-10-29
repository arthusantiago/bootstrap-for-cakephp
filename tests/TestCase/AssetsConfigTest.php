<?php
declare(strict_types=1);

namespace ArthuSantiago\BootstrapForCakePHP\Test\TestCase;

use ArthuSantiago\BootstrapForCakePHP\AssetsConfig;
use PHPUnit\Framework\TestCase;

class AssetsConfigTest extends TestCase
{
    public function testGetWebrootPathShouldReturnDefaultPath(): void
    {
        $path = AssetsConfig::getWebrootPath();

        $this->assertEquals('webroot', $path);
    }

    public function testGetPackagesShouldReturnArray(): void
    {
        $packages = AssetsConfig::getPackages();

        $this->assertIsArray($packages);
        $this->assertNotEmpty($packages);
    }

    public function testGetSupportedPackagesShouldContainBootstrap(): void
    {
        $packages = AssetsConfig::getSupportedPackages();

        $this->assertContains('twbs/bootstrap', $packages);
    }

    public function testGetSupportedPackagesShouldContainBootstrapIcons(): void
    {
        $packages = AssetsConfig::getSupportedPackages();

        $this->assertContains('twbs/bootstrap-icons', $packages);
    }

    public function testGetSupportedPackagesShouldContainPopperjs(): void
    {
        $packages = AssetsConfig::getSupportedPackages();

        $this->assertContains('popperjs/core', $packages);
    }

    public function testIsSupportedPackageShouldReturnTrueForBootstrap(): void
    {
        $result = AssetsConfig::isSupportedPackage('twbs/bootstrap');

        $this->assertTrue($result);
    }

    public function testIsSupportedPackageShouldReturnFalseForUnsupported(): void
    {
        $result = AssetsConfig::isSupportedPackage('vendor/unsupported-package');

        $this->assertFalse($result);
    }

    public function testGetPackageConfigShouldReturnConfigArray(): void
    {
        $config = AssetsConfig::getPackageConfig('twbs/bootstrap');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('source', $config);
        $this->assertArrayHasKey('assets', $config);
    }

    public function testGetPackageConfigShouldReturnNullForUnsupported(): void
    {
        $config = AssetsConfig::getPackageConfig('vendor/unsupported');

        $this->assertNull($config);
    }

    public function testBootstrapConfigShouldHaveCssAssets(): void
    {
        $config = AssetsConfig::getPackageConfig('twbs/bootstrap');

        $this->assertArrayHasKey('css', $config['assets']);
    }

    public function testBootstrapConfigShouldHaveJsAssets(): void
    {
        $config = AssetsConfig::getPackageConfig('twbs/bootstrap');

        $this->assertArrayHasKey('js', $config['assets']);
    }

    public function testBootstrapIconsConfigShouldHaveFontsAssets(): void
    {
        $config = AssetsConfig::getPackageConfig('twbs/bootstrap-icons');

        $this->assertArrayHasKey('fonts', $config['assets']);
    }
}
