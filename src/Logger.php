<?php
declare(strict_types=1);

namespace ArthuSantiago\BootstrapForCakePHP;

/**
 * Logger utility for file-based logging
 *
 * Handles logging of operations and errors to logs/bootstrapForCakephp.log
 */
class Logger
{
    /**
     * Log levels
     */
    public const LEVEL_ERROR = 'ERROR';
    public const LEVEL_WARNING = 'WARNING';
    public const LEVEL_INFO = 'INFO';
    public const LEVEL_DEBUG = 'DEBUG';

    /**
     * Default log file path (relative to project root)
     */
    private static string $logFile = 'logs/bootstrapForCakephp.log';

    /**
     * Whether to initialize logs directory
     */
    private static bool $initialized = false;

    /**
     * Initialize the logger by creating the logs directory if needed
     *
     * @return void
     */
    private static function initialize(): void
    {
        if (self::$initialized) {
            return;
        }

        $logDir = dirname(self::$logFile);

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        self::$initialized = true;
    }

    /**
     * Find the root directory of the project using this package
     *
     * Looks for composer.json or composer.lock starting from the vendor directory
     * and traversing up to find the project root.
     *
     * @return string Project root directory
     */
    private static function findProjectRoot(): string
    {
        // Start from the vendor directory (two levels up from this file)
        // src/Logger.php -> vendor/arthu-santiago/bootstrap-for-cakephp/src/
        $startDir = dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'vendor';

        if (!is_dir($startDir)) {
            // Fallback if vendor directory doesn't exist
            return getcwd();
        }

        // Go up from vendor directory to find project root
        $projectRoot = dirname($startDir);

        // Verify project root has composer.json or composer.lock
        if (file_exists($projectRoot . DIRECTORY_SEPARATOR . 'composer.json') ||
            file_exists($projectRoot . DIRECTORY_SEPARATOR . 'composer.lock')) {
            return $projectRoot;
        }

        // If not found, try using getcwd() as fallback
        return getcwd();
    }

    /**
     * Get the full path to the log file
     *
     * @return string
     */
    private static function getLogPath(): string
    {
        // Get the project root (where composer.json is located)
        $projectRoot = self::findProjectRoot();

        return $projectRoot . DIRECTORY_SEPARATOR . self::$logFile;
    }

    /**
     * Write a message to the log file
     *
     * @param string $level Log level (ERROR, WARNING, INFO, DEBUG)
     * @param string $message Log message
     * @return bool True if logged successfully, false otherwise
     */
    private static function write(string $level, string $message): bool
    {
        self::initialize();

        $logPath = self::getLogPath();
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] [{$level}] {$message}";

        $result = @file_put_contents($logPath, $logEntry . PHP_EOL, FILE_APPEND);

        return $result !== false;
    }

    /**
     * Log an error message
     *
     * @param string $message Error message
     * @return bool True if logged successfully
     */
    public static function error(string $message): bool
    {
        return self::write(self::LEVEL_ERROR, $message);
    }

    /**
     * Log a warning message
     *
     * @param string $message Warning message
     * @return bool True if logged successfully
     */
    public static function warning(string $message): bool
    {
        return self::write(self::LEVEL_WARNING, $message);
    }

    /**
     * Log an info message
     *
     * @param string $message Info message
     * @return bool True if logged successfully
     */
    public static function info(string $message): bool
    {
        return self::write(self::LEVEL_INFO, $message);
    }

    /**
     * Log a debug message
     *
     * @param string $message Debug message
     * @return bool True if logged successfully
     */
    public static function debug(string $message): bool
    {
        return self::write(self::LEVEL_DEBUG, $message);
    }

    /**
     * Set custom log file path
     *
     * @param string $logFile Log file path (relative to project root)
     * @return void
     */
    public static function setLogFile(string $logFile): void
    {
        self::$logFile = $logFile;
        self::$initialized = false;
    }

    /**
     * Get current log file path
     *
     * @return string
     */
    public static function getLogFile(): string
    {
        return self::getLogPath();
    }
}
