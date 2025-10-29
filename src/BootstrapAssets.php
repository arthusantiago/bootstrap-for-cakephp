<?php
declare(strict_types=1);

namespace ArthuSantiago\BootstrapForCakePHP;

use ArthuSantiago\BootstrapForCakePHP\Exception\FileOperationException;
use ArthuSantiago\BootstrapForCakePHP\Exception\UnsupportedPackageException;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use Composer\Script\Event;

/**
 * Bootstrap Assets Manager for CakePHP
 *
 * Automatically copies Bootstrap, Bootstrap Icons, and Popperjs assets to CakePHP webroot
 * when packages are installed or updated via Composer.
 *
 * This class can be extended to customize asset paths or behavior.
 *
 * @example
 * ```php
 * // In your CakePHP composer.json
 * "scripts": {
 *     "post-install-cmd": [
 *         "ArthuSantiago\\BootstrapForCakePHP\\BootstrapAssets::setupAssets"
 *     ],
 *     "post-update-cmd": [
 *         "ArthuSantiago\\BootstrapForCakePHP\\BootstrapAssets::setupAssets"
 *     ]
 * }
 * ```
 *
 * @see https://github.com/arthusantiago/bootstrap-for-cakephp
 */
class BootstrapAssets
{
    /**
     * IO interface for output messages
     *
     * @var IOInterface|null
     */
    protected static ?IOInterface $io = null;

    /**
     * Assets configuration class
     *
     * @var string
     */
    protected static string $configClass = AssetsConfig::class;

    /**
     * Set the IO interface for logging
     *
     * @param IOInterface $io
     * @return void
     */
    public static function setIO(IOInterface $io): void
    {
        self::$io = $io;
    }

    /**
     * Set custom configuration class
     *
     * @param string $configClass Must extend AssetsConfig
     * @return void
     */
    public static function setConfigClass(string $configClass): void
    {
        self::$configClass = $configClass;
    }

    /**
     * Write a message to output
     *
     * @param string $message
     * @param int $verbosity
     * @return void
     */
    protected static function write(string $message, int $verbosity = IOInterface::NORMAL): void
    {
        if (self::$io !== null) {
            self::$io->write($message, false, $verbosity);
        } else {
            echo $message . "\n";
        }
    }

    /**
     * Handle post-package installation event
     *
     * @param PackageEvent $event
     * @return void
     */
    public static function postPackageInstall(PackageEvent $event): void
    {
        self::setIO($event->getIO());
        $packageName = $event->getOperation()->getPackage()->getName();

        try {
            self::processPackage($packageName);
        } catch (FileOperationException | UnsupportedPackageException $e) {
            self::write("<error>{$e->getMessage()}</error>", IOInterface::QUIET);
        }
    }

    /**
     * Handle post-package update event
     *
     * @param PackageEvent $event
     * @return void
     */
    public static function postPackageUpdate(PackageEvent $event): void
    {
        self::setIO($event->getIO());
        $packageName = $event->getOperation()->getTargetPackage()->getName();

        try {
            self::processPackage($packageName);
        } catch (FileOperationException | UnsupportedPackageException $e) {
            self::write("<error>{$e->getMessage()}</error>", IOInterface::QUIET);
        }
    }

    /**
     * CLI command handler for manual asset copying
     *
     * Usage: composer copy-bootstrap-assets twbs/bootstrap twbs/bootstrap-icons popperjs/core
     *
     * @param Event $event
     * @return void
     */
    public static function setupAssets(Event $event): void
    {
        self::setIO($event->getIO());
        $arguments = $event->getArguments();

        if (empty($arguments)) {
            $supported = implode(', ', self::$configClass::getSupportedPackages());
            self::write(
                "<info>Copying Bootstrap assets automatically for supported packages:</info>"
            );
            self::write("<info>Supported: {$supported}</info>");

            foreach (self::$configClass::getSupportedPackages() as $packageName) {
                try {
                    self::processPackage($packageName);
                } catch (FileOperationException | UnsupportedPackageException $e) {
                    self::write("<comment>⚠ {$e->getMessage()}</comment>");
                }
            }

            return;
        }

        self::write("<info>Copying assets for specified packages...</info>");

        foreach ($arguments as $packageName) {
            try {
                self::processPackage($packageName);
            } catch (FileOperationException | UnsupportedPackageException $e) {
                self::write("<error>✗ {$e->getMessage()}</error>");
            }
        }
    }

    /**
     * Process a package and copy its assets
     *
     * @param string $packageName
     * @return void
     * @throws UnsupportedPackageException
     * @throws FileOperationException
     */
    public static function processPackage(string $packageName): void
    {
        $config = self::$configClass::getPackageConfig($packageName);

        if ($config === null) {
            throw new UnsupportedPackageException($packageName);
        }

        self::copyPackageAssets($packageName, $config);
    }

    /**
     * Copy assets for a specific package
     *
     * @param string $packageName
     * @param array $config Package configuration
     * @return void
     * @throws FileOperationException
     */
    protected static function copyPackageAssets(string $packageName, array $config): void
    {
        $webrootPath = self::$configClass::getWebrootPath();
        $baseSource = $config['source'] ?? '';
        $assets = $config['assets'] ?? [];

        foreach ($assets as $assetType => $assetConfig) {
            $source = $assetConfig['source'] ?? ($baseSource . $assetType . DIRECTORY_SEPARATOR);
            $destination = FileOperations::joinPaths(
                $webrootPath,
                $assetConfig['destination']
            );
            $files = $assetConfig['files'] ?? [];

            if (empty($files)) {
                continue;
            }

            // Delete old files
            FileOperations::deleteMultipleFiles($destination, $files);

            // Copy new files
            $results = FileOperations::copyMultipleFiles($source, $destination, $files);

            $copied = count(array_filter($results));
            $total = count($files);

            if ($copied === $total) {
                self::write(
                    "<info>✓ {$packageName} ({$assetType}) - {$copied}/{$total} files copied</info>"
                );
            } else {
                self::write(
                    "<comment>⚠ {$packageName} ({$assetType}) - {$copied}/{$total} files copied</comment>"
                );
            }
        }

        // Fix Bootstrap Icons CSS font paths if needed
        if ($packageName === 'twbs/bootstrap-icons') {
            self::fixBootstrapIconsCssPaths($webrootPath);
        }
    }

    /**
     * Fix font paths in Bootstrap Icons CSS
     * Changes relative paths from "fonts/" to "../fonts/" to match webroot structure
     *
     * @param string $webrootPath
     * @return void
     */
    protected static function fixBootstrapIconsCssPaths(string $webrootPath): void
    {
        $cssFile = FileOperations::joinPaths($webrootPath, 'css', 'bootstrap-icons.min.css');

        if (!file_exists($cssFile)) {
            return;
        }

        $content = file_get_contents($cssFile);
        if ($content === false) {
            return;
        }

        // Replace "fonts/" with "../fonts/" in font-face url declarations
        $updatedContent = preg_replace(
            '/url\("fonts\/bootstrap-icons\.woff/',
            'url("../fonts/bootstrap-icons/bootstrap-icons.woff',
            $content
        );

        if ($updatedContent !== null && $updatedContent !== $content) {
            file_put_contents($cssFile, $updatedContent);
            self::write(
                "<info>✓ Bootstrap Icons CSS font paths corrected</info>"
            );
        }
    }
}
