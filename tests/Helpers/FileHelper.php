<?php

namespace Maatwebsite\Excel\Tests\Helpers;

class FileHelper
{
    public static function absolutePath($fileName, $diskName)
    {
        return config('filesystems.disks.' . $diskName . '.root') . DIRECTORY_SEPARATOR . $fileName;
    }

    public static function recursiveDelete($fileName)
    {
        if (is_file($fileName)) {
            return @unlink($fileName);
        }

        if (is_dir($fileName)) {
            $scan = glob(rtrim($fileName, '/') . '/*');
            foreach ($scan as $path) {
                self::recursiveDelete($path);
            }

            return @rmdir($fileName);
        }
    }
}
