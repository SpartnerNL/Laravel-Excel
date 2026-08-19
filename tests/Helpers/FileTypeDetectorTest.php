<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Helpers;

use Maatwebsite\Excel\Exceptions\NoTypeDetectedException;
use Maatwebsite\Excel\Helpers\FileTypeDetector;
use Maatwebsite\Excel\Tests\TestCase;

final class FileTypeDetectorTest extends TestCase
{
    public function test_detect_returns_explicitly_passed_type_without_extension_lookup(): void
    {
        $this->assertSame('Xlsx', FileTypeDetector::detect('anything.csv', 'Xlsx'));
    }

    public function test_detect_resolves_type_from_file_path_extension(): void
    {
        $this->assertSame('Xlsx', FileTypeDetector::detect('test.xlsx'));
    }

    public function test_detect_resolves_type_from_uploaded_file_extension(): void
    {
        $file = $this->givenUploadedFile(
            __DIR__ . '/../Data/Disks/Local/import-users.xlsx',
            'upload.xlsx'
        );

        $this->assertSame('Xlsx', FileTypeDetector::detect($file));
    }

    public function test_detect_throws_when_file_path_has_no_extension(): void
    {
        $this->expectException(NoTypeDetectedException::class);

        FileTypeDetector::detect('filename-without-extension');
    }

    public function test_detect_strict_returns_type_for_known_extension(): void
    {
        $this->assertSame('Xlsx', FileTypeDetector::detectStrict('test.xlsx'));
    }

    public function test_detect_strict_throws_for_extension_not_in_config(): void
    {
        $this->expectException(NoTypeDetectedException::class);

        FileTypeDetector::detectStrict('test.unknown_format_xyz');
    }
}
