<?php
declare(strict_types=1);

namespace ArthuSantiago\BootstrapForCakePHP\Exception;

use ArthuSantiago\BootstrapForCakePHP\AssetsConfig;

/**
 * Exception thrown when an unsupported package is processed
 */
class UnsupportedPackageException extends BootstrapAssetsException
{
    public function __construct(string $packageName)
    {
        $supportedPackages = implode(', ', AssetsConfig::getSupportedPackages());
        parent::__construct(
            "Package '{$packageName}' is not supported. Supported packages: {$supportedPackages}"
        );
    }
}
