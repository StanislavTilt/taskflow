<?php

namespace App\Services;

use App\Enums\ProjectStatus;

class CsvParser
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function parse(string $content, string $delimiter = ';'): array
    {
        $rows   = array_filter(explode("\n", trim($content)));
        $header = str_getcsv(array_shift($rows), $delimiter);

        $results = [];
        foreach ($rows as $row) {
            $cols = str_getcsv($row, $delimiter);
            if (count($cols) !== count($header)) {
                continue;
            }
            $results[] = array_combine($header, $cols);
        }
        return $results;
    }

    private function processRecord($record){
        if(in_array($record['status'], ProjectStatus::cases()))
        {
            return $record;
        }
        $record['status'] = ProjectStatus::Active;
        return $record;
    }

}
