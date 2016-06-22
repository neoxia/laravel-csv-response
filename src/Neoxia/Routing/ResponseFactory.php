<?php

namespace Neoxia\Routing;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Routing\ResponseFactory as BaseResponseFactory;

class ResponseFactory extends BaseResponseFactory
{
    /**
     * Return a new CSV response from the application.
     *
     * @param  \Illuminate\Support\Collection|array|string  $data
     * @param  int  $status
     * @param  array  $headers
     * @return \Illuminate\Http\Response
     */
    public function csv($data, $status = 200, $headers = [])
    {
        if ($this->dataIsEmpty($data)) {
            return $this->make('No Content', 204);
        }

        $csv = $this->formatCsv($data);
        $headers = $this->createCsvHeaders($headers);

        return $this->make($csv, $status, $headers);
    }

    /**
     * Check if an array a string or a Collection is empty
     *
     * @param  \Illuminate\Support\Collection|array|string  $data
     * @return bool
     */
    protected function dataIsEmpty($data)
    {
        if ($data instanceof Collection) {
            return $data->isEmpty();
        } else {
            return empty($data);
        }
    }

    /**
     * Convert any data into a CSV string
     *
     * @param  \Illuminate\Support\Collection|array|string  $data
     * @return string
     */
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

    /**
     * Add a CSV header to an array based on data
     *
     * @param  array  $csvArray
     * @param  \Illuminate\Support\Collection|array  $data
     * @return void
     */
    protected function addHeaderToCsvArray(&$csvArray, $data)
    {
        $firstRowData = $this->getRowData($data[0]);

        if (Arr::isAssoc($firstRowData)) {
            $csvArray[0] = implode(';', array_keys($firstRowData));
        }
    }

    /**
     * Add CSV rows to an array based on data
     *
     * @param  array  $csvArray
     * @param  \Illuminate\Support\Collection|array  $data
     * @return void
     */
    protected function addRowsToCsvArray(&$csvArray, $data)
    {
        foreach ($data as $row) {
            $csvArray[] = implode(';', $this->getRowData($row));
        }
    }

    /**
     * Get an array of data for CSV from a mixed input
     *
     * @param  object|array  $row
     * @return array
     */
    protected function getRowData($row)
    {
        if (is_object($row)) {
            return $row->csvSerialize();
        } else {
            return $row;
        }
    }

    /**
     * Get HTTP headers for a CSV response
     *
     * @param  array  $customHeaders
     * @return void
     */
    protected function createCsvHeaders($customHeaders)
    {
        $baseHeaders = [
            'Content-Type' => 'text/csv; charset=WINDOWS-1252',
            'Content-Encoding' => 'WINDOWS-1252',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Description' => 'File Transfer',
        ];

        return array_merge($baseHeaders, $customHeaders);
    }
}
