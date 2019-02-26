<?php

namespace Maatwebsite\Excel\Files;

use Illuminate\Contracts\Filesystem\Filesystem;

/**
 * @method bool get(string $filename)
 * @method resource readStream(string $filename)
 * @method bool delete(string $filename)
 * @method bool exists(string $filename)
 */
class Disk
{
    /**
     * @var Filesystem
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

    /**
     * @param Filesystem  $disk
     * @param string|null $name
     * @param array       $diskOptions
     */
    public function __construct(Filesystem $disk, string $name = null, array $diskOptions = [])
    {
        $this->disk        = $disk;
        $this->name        = $name;
        $this->diskOptions = $diskOptions;
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->disk->{$name}(...$arguments);
    }

    /**
     * @param string          $destination
     * @param string|resource $contents
     *
     * @return bool
     */
    public function put(string $destination, $contents): bool
    {
        return $this->disk->put($destination, $contents, $this->diskOptions);
    }

    /**
     * @param TemporaryFile $source
     * @param string        $destination
     *
     * @return bool
     */
    public function copy(TemporaryFile $source, string $destination): bool
    {
        $readStream = $source->readStream();

        if (realpath($destination)) {
            $tempStream = fopen($destination, 'rb+');
            $success    = stream_copy_to_stream($readStream, $tempStream) !== false;
            fclose($tempStream);
        } else {
            $success = $this->put($destination, $readStream);
        }

        fclose($readStream);

        return $success;
    }

    /**
     * @param string $filename
     */
    public function touch(string $filename)
    {
        $this->disk->put($filename, '', $this->diskOptions);
    }
}
