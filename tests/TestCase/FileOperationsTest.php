<?php
declare(strict_types=1);

namespace ArthuSantiago\BootstrapForCakePHP\Test\TestCase;

use ArthuSantiago\BootstrapForCakePHP\Exception\FileOperationException;
use ArthuSantiago\BootstrapForCakePHP\FileOperations;
use PHPUnit\Framework\TestCase;

class FileOperationsTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/bootstrap-assets-test-' . uniqid();
        mkdir($this->tmpDir, 0755, true);
    }

    protected function tearDown(): void
    {
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
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    public function testCopyFileShouldCopyFileSuccessfully(): void
    {
        $sourceDir = $this->tmpDir . DIRECTORY_SEPARATOR . 'source';
        $destDir = $this->tmpDir . DIRECTORY_SEPARATOR . 'dest';
        mkdir($sourceDir);

        $sourceFile = $sourceDir . DIRECTORY_SEPARATOR . 'test.txt';
        $destFile = $destDir . DIRECTORY_SEPARATOR . 'test.txt';

        file_put_contents($sourceFile, 'test content');

        $result = FileOperations::copyFile($sourceFile, $destFile);

        $this->assertTrue($result);
        $this->assertFileExists($destFile);
        $this->assertEquals('test content', file_get_contents($destFile));
    }

    public function testCopyFileShouldCreateDestinationDirectory(): void
    {
        $sourceDir = $this->tmpDir . DIRECTORY_SEPARATOR . 'source';
        mkdir($sourceDir);

        $sourceFile = $sourceDir . DIRECTORY_SEPARATOR . 'test.txt';
        $destFile = $this->tmpDir . DIRECTORY_SEPARATOR . 'nested' . DIRECTORY_SEPARATOR . 'dir' . DIRECTORY_SEPARATOR . 'test.txt';

        file_put_contents($sourceFile, 'test content');

        FileOperations::copyFile($sourceFile, $destFile);

        $this->assertFileExists($destFile);
    }

    public function testCopyFileShouldThrowExceptionIfSourceNotFound(): void
    {
        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessage('Source file not found');

        FileOperations::copyFile('/non/existent/file.txt', 'dest.txt');
    }

    public function testCopyMultipleFilesShouldCopyAllFiles(): void
    {
        $sourceDir = $this->tmpDir . DIRECTORY_SEPARATOR . 'source';
        $destDir = $this->tmpDir . DIRECTORY_SEPARATOR . 'dest';
        mkdir($sourceDir);
        mkdir($destDir);

        // Create source files
        file_put_contents($sourceDir . DIRECTORY_SEPARATOR . 'file1.txt', 'content1');
        file_put_contents($sourceDir . DIRECTORY_SEPARATOR . 'file2.txt', 'content2');

        $results = FileOperations::copyMultipleFiles(
            $sourceDir,
            $destDir,
            ['file1.txt', 'file2.txt']
        );

        $this->assertTrue($results['file1.txt']);
        $this->assertTrue($results['file2.txt']);
        $this->assertFileExists($destDir . DIRECTORY_SEPARATOR . 'file1.txt');
        $this->assertFileExists($destDir . DIRECTORY_SEPARATOR . 'file2.txt');
    }

    public function testDeleteFileShouldDeleteFile(): void
    {
        $dir = $this->tmpDir . DIRECTORY_SEPARATOR . 'files';
        mkdir($dir);
        $file = $dir . DIRECTORY_SEPARATOR . 'test.txt';

        file_put_contents($file, 'content');
        $this->assertFileExists($file);

        $result = FileOperations::deleteFile($file);

        $this->assertTrue($result);
        $this->assertFileDoesNotExist($file);
    }

    public function testDeleteFileShouldReturnFalseIfFileDoesNotExist(): void
    {
        $result = FileOperations::deleteFile('/non/existent/file.txt');

        $this->assertFalse($result);
    }

    public function testNormalizePathShouldAddTrailingSlash(): void
    {
        $path = FileOperations::normalizePath('/some/path');

        $this->assertTrue(str_ends_with($path, DIRECTORY_SEPARATOR));
    }

    public function testNormalizePathShouldNotAddTrailingSlashWhenFalse(): void
    {
        $path = FileOperations::normalizePath('/some/path', false);

        $this->assertFalse(str_ends_with($path, DIRECTORY_SEPARATOR));
    }

    public function testJoinPathsShouldJoinPathSegments(): void
    {
        $path = FileOperations::joinPaths('webroot', 'css', 'bootstrap.min.css');

        $this->assertStringContainsString('webroot', $path);
        $this->assertStringContainsString('css', $path);
        $this->assertStringContainsString('bootstrap.min.css', $path);
    }

    public function testJoinPathsShouldFilterEmptySegments(): void
    {
        $path = FileOperations::joinPaths('webroot', '', 'css');

        $this->assertFalse(str_contains($path, DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR));
    }

    public function testCopyFileShouldApplyDefaultPermissionsWhenNotSpecified(): void
    {
        $sourceDir = $this->tmpDir . DIRECTORY_SEPARATOR . 'source';
        $destDir = $this->tmpDir . DIRECTORY_SEPARATOR . 'dest';
        mkdir($sourceDir);

        $sourceFile = $sourceDir . DIRECTORY_SEPARATOR . 'test.txt';
        $destFile = $destDir . DIRECTORY_SEPARATOR . 'test.txt';

        file_put_contents($sourceFile, 'test content');

        // Copy without specifying permissions (should use default 0750)
        FileOperations::copyFile($sourceFile, $destFile);

        $this->assertFileExists($destFile);

        // Get file permissions
        $perms = fileperms($destFile) & 0777;

        // Verify default permissions (0750)
        $this->assertEquals(0750, $perms, sprintf(
            'Expected permissions 0750, got %04o',
            $perms
        ));
    }

    public function testCopyFileShouldApplyCustomPermissionsWhenSpecified(): void
    {
        $sourceDir = $this->tmpDir . DIRECTORY_SEPARATOR . 'source';
        $destDir = $this->tmpDir . DIRECTORY_SEPARATOR . 'dest';
        mkdir($sourceDir);

        $sourceFile = $sourceDir . DIRECTORY_SEPARATOR . 'test.txt';
        $destFile = $destDir . DIRECTORY_SEPARATOR . 'test.txt';

        file_put_contents($sourceFile, 'test content');

        // Copy with custom permissions (0644)
        FileOperations::copyFile($sourceFile, $destFile, 0644);

        $this->assertFileExists($destFile);

        // Get file permissions
        $perms = fileperms($destFile) & 0777;

        // Verify custom permissions (0644)
        $this->assertEquals(0644, $perms, sprintf(
            'Expected permissions 0644, got %04o',
            $perms
        ));
    }

    public function testCopyMultipleFilesShouldApplyDefaultPermissionsWhenNotSpecified(): void
    {
        $sourceDir = $this->tmpDir . DIRECTORY_SEPARATOR . 'source';
        $destDir = $this->tmpDir . DIRECTORY_SEPARATOR . 'dest';
        mkdir($sourceDir);
        mkdir($destDir);

        // Create source files
        file_put_contents($sourceDir . DIRECTORY_SEPARATOR . 'file1.txt', 'content1');
        file_put_contents($sourceDir . DIRECTORY_SEPARATOR . 'file2.txt', 'content2');

        // Copy without specifying permissions
        $results = FileOperations::copyMultipleFiles(
            $sourceDir,
            $destDir,
            ['file1.txt', 'file2.txt']
        );

        $this->assertTrue($results['file1.txt']);
        $this->assertTrue($results['file2.txt']);

        // Verify permissions for both files (should be 0750)
        $file1Perms = fileperms($destDir . DIRECTORY_SEPARATOR . 'file1.txt') & 0777;
        $file2Perms = fileperms($destDir . DIRECTORY_SEPARATOR . 'file2.txt') & 0777;

        $this->assertEquals(0750, $file1Perms, sprintf(
            'Expected permissions 0750 for file1.txt, got %04o',
            $file1Perms
        ));
        $this->assertEquals(0750, $file2Perms, sprintf(
            'Expected permissions 0750 for file2.txt, got %04o',
            $file2Perms
        ));
    }

    public function testCopyMultipleFilesShouldApplyCustomPermissionsWhenSpecified(): void
    {
        $sourceDir = $this->tmpDir . DIRECTORY_SEPARATOR . 'source';
        $destDir = $this->tmpDir . DIRECTORY_SEPARATOR . 'dest';
        mkdir($sourceDir);
        mkdir($destDir);

        // Create source files
        file_put_contents($sourceDir . DIRECTORY_SEPARATOR . 'file1.txt', 'content1');
        file_put_contents($sourceDir . DIRECTORY_SEPARATOR . 'file2.txt', 'content2');

        // Copy with custom permissions (0600)
        $results = FileOperations::copyMultipleFiles(
            $sourceDir,
            $destDir,
            ['file1.txt', 'file2.txt'],
            0600
        );

        $this->assertTrue($results['file1.txt']);
        $this->assertTrue($results['file2.txt']);

        // Verify permissions for both files (should be 0600)
        $file1Perms = fileperms($destDir . DIRECTORY_SEPARATOR . 'file1.txt') & 0777;
        $file2Perms = fileperms($destDir . DIRECTORY_SEPARATOR . 'file2.txt') & 0777;

        $this->assertEquals(0600, $file1Perms, sprintf(
            'Expected permissions 0600 for file1.txt, got %04o',
            $file1Perms
        ));
        $this->assertEquals(0600, $file2Perms, sprintf(
            'Expected permissions 0600 for file2.txt, got %04o',
            $file2Perms
        ));
    }
}
