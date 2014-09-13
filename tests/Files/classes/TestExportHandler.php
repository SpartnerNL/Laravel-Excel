<?php

use Maatwebsite\Excel\Files\ExportHandler;

class TestExportHandler implements ExportHandler {

    /**
     * Handle
     * @param $file
     * @return mixed|void
     */
    public function handle($file)
    {
        return 'exported';
    }

} 