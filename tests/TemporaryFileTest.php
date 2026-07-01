<?php

namespace Maatwebsite\Excel\Tests;

use Maatwebsite\Excel\Files\TemporaryFileFactory;
use Maatwebsite\Excel\Tests\Helpers\FileHelper;

class TemporaryFileTest extends TestCase
{
    private $defaultDirectoryPermissions;

    private $defaultFilePermissions;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $path = FileHelper::absolutePath('rights-test-permissions', 'local');
        mkdir($path);
        $this->defaultDirectoryPermissions = substr(sprintf('%o', fileperms($path)), -4);

        $filePath = $path . DIRECTORY_SEPARATOR . 'file-permissions';
        touch($filePath);
        $this->defaultFilePermissions = substr(sprintf('%o', fileperms($filePath)), -4);

        @unlink($filePath);
        @rmdir($path);
    }

    public function test_can_use_default_rights(): void
    {
        $path = FileHelper::absolutePath('rights-test', 'local');
        FileHelper::recursiveDelete($path);

        config()->set('excel.temporary_files.local_path', $path);

        $temporaryFileFactory = app(TemporaryFileFactory::class);

        $temporaryFile = $temporaryFileFactory->makeLocal(null, 'txt');
        $temporaryFile->put('data-set');

        $this->assertFileExists($temporaryFile->getLocalPath());
        $this->assertSame($this->defaultDirectoryPermissions, substr(sprintf('%o', fileperms(dirname($temporaryFile->getLocalPath()))), -4));
        $this->assertSame($this->defaultFilePermissions, substr(sprintf('%o', fileperms($temporaryFile->getLocalPath())), -4));
    }

    public function test_can_use_dir_rights(): void
    {
        $path = FileHelper::absolutePath('rights-test', 'local');
        FileHelper::recursiveDelete($path);

        config()->set('excel.temporary_files.local_path', $path);
        config()->set('excel.temporary_files.local_permissions.dir', 0o700);

        $temporaryFileFactory = app(TemporaryFileFactory::class);

        $temporaryFile = $temporaryFileFactory->makeLocal(null, 'txt');
        $temporaryFile->put('data-set');

        $this->assertFileExists($temporaryFile->getLocalPath());
        $this->assertSame('0700', substr(sprintf('%o', fileperms(dirname($temporaryFile->getLocalPath()))), -4));
        $this->assertSame($this->defaultFilePermissions, substr(sprintf('%o', fileperms($temporaryFile->getLocalPath())), -4));
    }

    public function test_can_use_file_rights(): void
    {
        $path = FileHelper::absolutePath('rights-test', 'local');
        FileHelper::recursiveDelete($path);

        config()->set('excel.temporary_files.local_path', $path);
        config()->set('excel.temporary_files.local_permissions.file', 0o600);

        $temporaryFileFactory = app(TemporaryFileFactory::class);

        $temporaryFile = $temporaryFileFactory->makeLocal(null, 'txt');
        $temporaryFile->put('data-set');

        $this->assertFileExists($temporaryFile->getLocalPath());
        $this->assertSame($this->defaultDirectoryPermissions, substr(sprintf('%o', fileperms(dirname($temporaryFile->getLocalPath()))), -4));
        $this->assertSame('0600', substr(sprintf('%o', fileperms($temporaryFile->getLocalPath())), -4));
    }
}
