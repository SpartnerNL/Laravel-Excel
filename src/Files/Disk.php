<?php

namespace Maatwebsite\Excel\Files;

use Illuminate\Contracts\Filesystem\Filesystem as IlluminateFilesystem;

/**
 * @method bool get(string $filename)
 * @method resource readStream(string $filename)
 * @method bool delete(string $filename)
 * @method bool exists(string $filename)
 */
class Disk
{
    /**
     * @var IlluminateFilesystem
     */
    protected $disk;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var array
     */
    protected $diskOptions;

    public function __construct(IlluminateFilesystem $disk, ?string $name = null, array $diskOptions = [])
    {
        $this->disk        = $disk;
        $this->name        = $name;
        $this->diskOptions = $diskOptions;
    }

    /**
     * @param  string  $name
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->disk->{$name}(...$arguments);
    }

    /**
     * @param  string|resource  $contents
     */
    public function put(string $destination, $contents): bool
    {
        return $this->disk->put($destination, $contents, $this->diskOptions);
    }

    public function copy(TemporaryFile $source, string $destination): bool
    {
        $readStream = $source->readStream();

        if (!is_resource($readStream)) {
            return false;
        }

        if (realpath($destination)) {
            $tempStream = fopen($destination, 'rb+');
            $success    = stream_copy_to_stream($readStream, $tempStream) !== false;

            if (is_resource($tempStream)) {
                fclose($tempStream);
            }
        } else {
            $success = $this->put($destination, $readStream);
        }

        if (is_resource($readStream)) {
            fclose($readStream);
        }

        return $success;
    }

    public function touch(string $filename)
    {
        $this->disk->put($filename, '', $this->diskOptions);
    }
}
