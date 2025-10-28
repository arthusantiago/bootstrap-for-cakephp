<?php
declare(strict_types=1);

namespace ArthuSantiago\BootstrapForCakePHP;

use Composer\Installer\PackageEvent;
use Composer\Script\Event;

/**
 * Manages Bootstrap and Bootstrap Icons assets copying to CakePHP webroot
 *
 * This tool automatically copies Bootstrap, Bootstrap Icons, and Popperjs files
 * from vendor/ to the appropriate webroot/ directories when packages are installed
 * or updated via Composer.
 *
 * @see https://github.com/arthusantiago/bootstrap-for-cakephp
 */
class BootstrapAssets
{
    /**
     * Bootstrap Icons CSS
     */
    private static string $cssOrigemBootstrapIcons = 'vendor/twbs/bootstrap-icons/font/bootstrap-icons.min.css';
    private static string $cssDestinoBootstrapIcons = 'webroot/css/bootstrap-icons.min.css';
    private static string $cssOrigemBootstrapIconsFonts = 'vendor/twbs/bootstrap-icons/font/fonts/';
    private static string $cssDestinoBootstrapIconsFonts = 'webroot/css/fonts/';

    /**
     * @var array Bootstrap Icons font files
     */
    private static array $arquivosBootstrapIconsFonts = [
        'bootstrap-icons.woff',
        'bootstrap-icons.woff2',
    ];

    /**
     * Bootstrap dist files
     */
    private static string $vendorBootstrap = 'vendor/twbs/bootstrap/dist/';
    private static string $pathDestinoJS = 'webroot/js';
    private static string $pathDestinoCSS = 'webroot/css';

    /**
     * @var array Bootstrap CSS files to copy
     */
    private static array $fileDestinoBootstrapCSS = [
        'bootstrap.min.css',
        'bootstrap.min.css.map',
    ];

    /**
     * @var array Bootstrap JS files to copy
     */
    private static array $fileDestinoBootstrapJS = [
        'bootstrap.min.js',
        'bootstrap.min.js.map',
    ];

    /**
     * Popperjs library
     */
    private static string $popperOrigem = 'vendor/popperjs/core/dist/umd/popper.min.js';
    private static string $popperDestino = 'webroot/js/popper.min.js';

    /**
     * Handle post-package installation event
     *
     * @param PackageEvent $event
     * @return void
     */
    public static function postPackageInstall(PackageEvent $event): void
    {
        $nomePacote = $event->getOperation()->getPackage()->getName();
        self::executar($nomePacote);
    }

    /**
     * Handle post-package update event
     *
     * @param PackageEvent $event
     * @return void
     */
    public static function postPackageUpdate(PackageEvent $event): void
    {
        $nomePacote = $event->getOperation()->getTargetPackage()->getName();
        self::executar($nomePacote);
    }

    /**
     * CLI command handler for manual asset copying
     *
     * Usage: composer copy-bootstrap-assets twbs/bootstrap twbs/bootstrap-icons popperjs/core
     *
     * @param Event $event
     * @return void
     */
    public static function executarCli(Event $event): void
    {
        $arguments = $event->getArguments();
        if (empty($arguments)) {
            echo "Usage: composer copy-bootstrap-assets <package-name> [<package-name>]\n";
            return;
        }

        foreach ($arguments as $pacote) {
            self::executar($pacote);
        }
    }

    /**
     * Main method to handle asset copying for a specific package
     *
     * @param string $nomePacote Package name (e.g., 'twbs/bootstrap')
     * @return void
     */
    public static function executar(string $nomePacote): void
    {
        match ($nomePacote) {
            'twbs/bootstrap-icons' => self::copiarBootstrapIcons(),
            'twbs/bootstrap' => self::copiarBootstrap(),
            'popperjs/core' => self::copiarPopper(),
            default => null,
        };
    }

    /**
     * Copy Bootstrap Icons CSS and fonts
     *
     * @return void
     */
    private static function copiarBootstrapIcons(): void
    {
        // Copy CSS file
        self::excluiArquivo(self::$cssDestinoBootstrapIcons);
        self::copiarArquivo(self::$cssOrigemBootstrapIcons, self::$cssDestinoBootstrapIcons);

        // Copy font files
        self::excluirMultiplosArquivo(self::$cssDestinoBootstrapIconsFonts, self::$arquivosBootstrapIconsFonts);
        self::copiarMultiplosArquivo(
            self::$cssOrigemBootstrapIconsFonts,
            self::$cssDestinoBootstrapIconsFonts,
            self::$arquivosBootstrapIconsFonts
        );

        echo "✓ Bootstrap Icons assets copied successfully\n";
    }

    /**
     * Copy Bootstrap CSS and JS files
     *
     * @return void
     */
    private static function copiarBootstrap(): void
    {
        // Remove old files
        self::excluirMultiplosArquivo(self::$pathDestinoCSS, self::$fileDestinoBootstrapCSS);
        self::excluirMultiplosArquivo(self::$pathDestinoJS, self::$fileDestinoBootstrapJS);

        // Copy new files
        self::copiarMultiplosArquivo(
            self::$vendorBootstrap . 'css/',
            self::$pathDestinoCSS,
            self::$fileDestinoBootstrapCSS
        );

        self::copiarMultiplosArquivo(
            self::$vendorBootstrap . 'js/',
            self::$pathDestinoJS,
            self::$fileDestinoBootstrapJS
        );

        echo "✓ Bootstrap assets copied successfully\n";
    }

    /**
     * Copy Popperjs library
     *
     * @return void
     */
    private static function copiarPopper(): void
    {
        self::excluiArquivo(self::$popperDestino);
        self::copiarArquivo(self::$popperOrigem, self::$popperDestino);

        echo "✓ Popperjs assets copied successfully\n";
    }

    /**
     * Copy a single file
     *
     * @param string $arquivoOrigem Source file path
     * @param string $arquivoDestino Destination file path
     * @return bool True if file was copied, false otherwise
     */
    private static function copiarArquivo(string $arquivoOrigem, string $arquivoDestino): bool
    {
        if (!is_file($arquivoOrigem)) {
            return false;
        }

        // Ensure destination directory exists
        $destDir = dirname($arquivoDestino);
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        return copy($arquivoOrigem, $arquivoDestino);
    }

    /**
     * Copy multiple files
     *
     * @param string $pathOrigem Source directory
     * @param string $pathDestino Destination directory
     * @param array $arquivos File names to copy
     * @return void
     */
    private static function copiarMultiplosArquivo(string $pathOrigem, string $pathDestino, array $arquivos): void
    {
        foreach ($arquivos as $arquivo) {
            $pathCompletoOrigem = $pathOrigem . $arquivo;
            $pathCompletoDestino = $pathDestino . $arquivo;
            self::copiarArquivo($pathCompletoOrigem, $pathCompletoDestino);
        }
    }

    /**
     * Delete a file
     *
     * @param string $arquivo File path
     * @return bool True if deleted, false otherwise
     */
    private static function excluiArquivo(string $arquivo): bool
    {
        if (!is_file($arquivo)) {
            return false;
        }

        return unlink($arquivo);
    }

    /**
     * Delete multiple files
     *
     * @param string $pathArquivos Directory containing files
     * @param array $arquivos File names to delete
     * @return void
     */
    private static function excluirMultiplosArquivo(string $pathArquivos, array $arquivos): void
    {
        foreach ($arquivos as $arquivo) {
            self::excluiArquivo($pathArquivos . $arquivo);
        }
    }
}
