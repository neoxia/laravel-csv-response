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
     * @param  string  $encoding
     * @return \Illuminate\Http\Response
     */
    public function csv($data, $status = 200, $headers = [], $encoding = 'WINDOWS-1252')
    {
        if ($this->dataIsEmpty($data)) {
            return $this->make('No Content', 204);
        }

        $csv = $this->formatCsv($data, $encoding);
        $headers = $this->createCsvHeaders($headers, $encoding);

        return $this->make($csv, $status, $headers);
    }

    /**
     * Check if the data set is empty
     *
     * @param  mixed  $data
     * @return bool
     */
    protected function dataIsEmpty($data)
    {
        if (method_exists($data, 'isEmpty')) {
            return $data->isEmpty();
        }

        return empty($data);
    }

    /**
     * Convert any data into a CSV string
     *
     * @param  \Illuminate\Support\Collection|array|string  $data
     * @param  string  $encoding
     * @return string
     */
    protected function formatCsv($data, $encoding)
    {
        if (is_string($data)) {
            $csv = $data;
        } else {        
            $csvArray = [];

            $this->addHeaderToCsvArray($csvArray, $data);
            $this->addRowsToCsvArray($csvArray, $data);

            $csv = implode("\r\n", $csvArray);
        }

        return mb_convert_encoding($csv, $encoding);
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
            $rowData = array_keys($firstRowData);

            $csvArray[0] = $this->rowDataToCsvString($rowData);
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
            $rowData = $this->getRowData($row);

            $csvArray[] = $this->rowDataToCsvString($rowData);
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
        }

        return $row;
    }

    /**
     * Escape quotes and join cells of an array to make a csv row string
     *
     * @param  array  $row
     * @return string
     */
    protected function rowDataToCsvString($row)
    {
        array_walk($row, function (&$cell) {
            $cell = '"' . str_replace('"', '""', $cell) . '"';
        });

        return implode(';', $row);
    }

    /**
     * Get HTTP headers for a CSV response
     *
     * @param  array  $customHeaders
     * @param  string  $encoding
     * @return void
     */
    protected function createCsvHeaders($customHeaders, $encoding)
    {
        $baseHeaders = [
            'Content-Type' => 'text/csv; charset=' . $encoding,
            'Content-Encoding' => $encoding,
            'Content-Transfer-Encoding' => 'binary',
            'Content-Description' => 'File Transfer',
        ];

        return array_merge($baseHeaders, $customHeaders);
    }
}
