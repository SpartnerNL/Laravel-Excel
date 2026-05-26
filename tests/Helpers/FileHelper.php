<?php

namespace Maatwebsite\Excel\Tests\Helpers;

class FileHelper
{
    public static function absolutePath(string $fileName, string $diskName): string
    {
        return config('filesystems.disks.' . $diskName . '.root') . DIRECTORY_SEPARATOR . $fileName;
    }

    public static function recursiveDelete($fileName): ?bool
    {
        if (is_file($fileName)) {
            return @unlink($fileName);
        }

        if (is_dir($fileName)) {
            $scan = glob(rtrim((string) $fileName, '/') . '/*');
            foreach ($scan as $path) {
                self::recursiveDelete($path);
            }

            return @rmdir($fileName);
        }
    }
}
