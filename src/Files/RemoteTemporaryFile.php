<?php

namespace Maatwebsite\Excel\Files;

class RemoteTemporaryFile extends TemporaryFile
{
    /**
     * @var Disk
     */
    private $disk;

    /**
     * @var string
     */
    private $filename;

    /**
     * @param Disk   $disk
     * @param string $filename
     */
    public function __construct(Disk $disk, string $filename)
    {
        $this->disk     = $disk;
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getLocalPath(): string
    {
        return $this->copy()->getLocalPath();
    }

    /**
     * @return LocalTemporaryFile
     */
    private function copy(): LocalTemporaryFile
    {
        return $this->disk->copyToLocalTempFolder(
            $this->filename
        );
    }

    /**
     * @return bool
     */
    public function exists(): bool
    {
        return $this->disk->exists($this->filename);
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        return $this->disk->delete($this->filename);
    }

    /**
     * Store on remote disk.
     */
    public function store()
    {
        $this->disk->put(
            $this->getLocalPath(),
            $this->filename
        );
    }
}