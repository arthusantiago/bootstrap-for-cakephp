<?php
declare(strict_types=1);

namespace ArthuSantiago\BootstrapForCakePHP;

use ArthuSantiago\BootstrapForCakePHP\Exception\FileOperationException;
use ArthuSantiago\BootstrapForCakePHP\Logger;

/**
 * Utility class for file operations
 *
 * Handles copying and deleting files with proper error handling and path management.
 */
class FileOperations
{
    /**
     * Copy a single file from source to destination
     *
     * Creates destination directory if it doesn't exist.
     *
     * @param string $source Source file path
     * @param string $destination Destination file path
     * @param int $permissions File permissions in octal format (default: 0750)
     * @return bool True if copied successfully
     * @throws FileOperationException
     */
    public static function copyFile(string $source, string $destination, int $permissions = 0750): bool
    {
        if (!is_file($source)) {
            $errorMessage = "Source file not found: {$source}";
            Logger::error($errorMessage);
            throw new FileOperationException($errorMessage);
        }

        $destinationDir = dirname($destination);
        if (!is_dir($destinationDir)) {
            if (!mkdir($destinationDir, 0755, true)) {
                $errorMessage = "Failed to create destination directory: {$destinationDir}";
                Logger::error($errorMessage);
                throw new FileOperationException($errorMessage);
            }
        }

        if (!copy($source, $destination)) {
            $errorMessage = "Failed to copy file from {$source} to {$destination}";
            Logger::error($errorMessage);
            throw new FileOperationException($errorMessage);
        }

        // Set file permissions
        if (!chmod($destination, $permissions)) {
            $errorMessage = "Failed to set permissions for file: {$destination}";
            Logger::error($errorMessage);
            throw new FileOperationException($errorMessage);
        }

        return true;
    }

    /**
     * Copy multiple files from source directory to destination directory
     *
     * @param string $sourceDir Source directory
     * @param string $destinationDir Destination directory
     * @param array<string> $files File names to copy
     * @param int $permissions File permissions in octal format (default: 0750)
     * @return array<string, bool> Array of [filename => success]
     * @throws FileOperationException
     */
    public static function copyMultipleFiles(
        string $sourceDir,
        string $destinationDir,
        array $files,
        int $permissions = 0750
    ): array {
        if (empty($files)) {
            return [];
        }

        $results = [];
        $sourceDir = self::normalizePath($sourceDir);
        $destinationDir = self::normalizePath($destinationDir);

        foreach ($files as $file) {
            $sourcePath = $sourceDir . $file;
            $destinationPath = $destinationDir . $file;

            try {
                self::copyFile($sourcePath, $destinationPath, $permissions);
                $results[$file] = true;
            } catch (FileOperationException $e) {
                // Log with more context about what went wrong
                $errorMsg = $e->getMessage();
                Logger::error("File copy failed for {$file}: {$errorMsg} (Source: {$sourcePath})");
                $results[$file] = false;
            }
        }

        return $results;
    }

    /**
     * Delete a file
     *
     * @param string $file File path
     * @return bool True if deleted, false if file doesn't exist
     * @throws FileOperationException
     */
    public static function deleteFile(string $file): bool
    {
        if (!is_file($file)) {
            return false;
        }

        if (!unlink($file)) {
            $errorMessage = "Failed to delete file: {$file}";
            Logger::error($errorMessage);
            throw new FileOperationException($errorMessage);
        }

        return true;
    }

    /**
     * Delete multiple files
     *
     * @param string $directory Directory containing files
     * @param array<string> $files File names to delete
     * @return array<string, bool> Array of [filename => deleted]
     */
    public static function deleteMultipleFiles(
        string $directory,
        array $files
    ): array {
        if (empty($files)) {
            return [];
        }

        $results = [];
        $directory = self::normalizePath($directory);

        foreach ($files as $file) {
            $filePath = $directory . $file;
            try {
                $results[$file] = self::deleteFile($filePath);
            } catch (FileOperationException $e) {
                // Log with more context about what went wrong
                $errorMsg = $e->getMessage();
                Logger::error("File deletion failed for {$file}: {$errorMsg} (Path: {$filePath})");
                $results[$file] = false;
            }
        }

        return $results;
    }

    /**
     * Normalize a path by ensuring proper directory separators and trailing slash
     *
     * @param string $path Path to normalize
     * @param bool $trailingSlash Whether to add trailing slash
     * @return string Normalized path
     */
    public static function normalizePath(string $path, bool $trailingSlash = true): string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $path = rtrim($path, DIRECTORY_SEPARATOR);

        if ($trailingSlash) {
            $path .= DIRECTORY_SEPARATOR;
        }

        return $path;
    }

    /**
     * Join path segments
     *
     * @param string ...$segments Path segments
     * @return string Joined path
     */
    public static function joinPaths(string ...$segments): string
    {
        $segments = array_filter($segments, static fn ($s) => !empty($s));

        if (empty($segments)) {
            return '';
        }

        return implode(DIRECTORY_SEPARATOR, $segments);
    }
}
