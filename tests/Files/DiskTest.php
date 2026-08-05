<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Files;

use Illuminate\Contracts\Filesystem\Filesystem;
use Maatwebsite\Excel\Files\Disk;
use Maatwebsite\Excel\Files\LocalTemporaryFile;
use Maatwebsite\Excel\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

final class DiskTest extends TestCase
{
    private readonly Disk $disk;

    private readonly Filesystem&MockInterface $filesystem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = Mockery::mock(Filesystem::class);
        $this->disk       = new Disk($this->filesystem);
    }

    public function test_it_handles_closed_resource(): void
    {
        $this->filesystem->shouldReceive('put')
            ->with('test', Mockery::type('resource'), [])
            ->andReturnUsing(fn (string $destination, $contents): bool => fclose($contents))
            ->once();

        $this->disk->copy(new LocalTemporaryFile(tempnam(sys_get_temp_dir(), 'laravel-excel')), 'test');
    }

    public function test_it_closes_an_open_resource(): void
    {
        $this->filesystem->shouldReceive('put')
            ->with('test', Mockery::type('resource'), [])
            ->andReturnTrue()
            ->once();

        $this->disk->copy(new LocalTemporaryFile(tempnam(sys_get_temp_dir(), 'laravel-excel')), 'test');
    }
}
