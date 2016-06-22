<?php

namespace Neoxia\Routing;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Routing\ResponseFactory as BaseResponseFactory;

class ResponseFactory extends BaseResponseFactory
{
    public function csv($data)
    {
        if ($this->dataIsEmpty($data)) {
            return $this->make('No Content', 204);
        }

        $csv = $this->formatCsv($data);
        $headers = $this->createCsvHeaders();

        return $this->make($csv, 200, $headers);
    }

    protected function dataIsEmpty($data)
    {
        if ($data instanceof Collection) {
            return $data->isEmpty();
        } else {
            return empty($data);
        }
    }

    protected function formatCsv($data)
    {
        if (is_string($data)) {
            return $data;
        }

        $csvArray = [];

        $this->addHeaderToCsvArray($csvArray, $data);
        $this->addRowsToCsvArray($csvArray, $data);

        return implode("\r\n", $csvArray);
    }

    protected function addHeaderToCsvArray(&$csvArray, $data)
    {
        $firstRowData = $this->getRowData($data[0]);

        if (Arr::isAssoc($firstRowData)) {
            $csvArray[0] = implode(';', array_keys($firstRowData));
        }
    }

    protected function addRowsToCsvArray(&$csvArray, $data)
    {
        foreach ($data as $row) {
            $csvArray[] = implode(';', $this->getRowData($row));
        }
    }

    protected function getRowData($row)
    {
        if (is_object($row)) {
            return $row->csvSerialize();
        } else {
            return $row;
        }
    }

    protected function createCsvHeaders()
    {
        return [
            'Content-Type' => 'text/csv; charset=WINDOWS-1252',
            'Content-Encoding' => 'WINDOWS-1252',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Description' => 'File Transfer',
        ];
    }
}
