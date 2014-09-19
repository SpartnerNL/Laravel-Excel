<?php

use Maatwebsite\Excel\Files\ImportHandler;

class TestImportHandler implements ImportHandler {

    /**
     * Handle
     * @param $file
     * @return mixed|void
     */
    public function handle($file)
    {
        return $file->get();
    }

} 