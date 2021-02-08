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
     * @param  array  $options
     * @return \Illuminate\Http\Response
     */
    public function csv($data, $status = 200, array $headers = [], array $options = [])
    {
        if ($this->dataIsEmpty($data)) {
            return $this->make('No Content', 204);
        }

        $options = $this->parseOptions($options);

        $csv = $this->formatCsv($data, $options);
        $headers = $this->createCsvHeaders($headers, $options);

        return $this->make($csv, $status, $headers);
    }

    /**
     * Return an array of options with default ones if not set
     *
     * @param  array  $customOptions
     * @return array
     */
    public function parseOptions(array $customOptions)
    {
        $baseOptions = [
            'encoding' => 'WINDOWS-1252',
            'delimiter' => ';',
            'quoted' => true,
            'include_header' => true,
        ];

        return array_merge($baseOptions, $customOptions);
    }

    /**
     * Check if the data set is empty
     *
     * @param  \Illuminate\Support\Collection|array|string  $data
     * @return bool
     */
    protected function dataIsEmpty($data)
    {
        if (is_object($data) && method_exists($data, 'isEmpty')) {
            return $data->isEmpty();
        }

        return empty($data);
    }

    /**
     * Convert any data into a CSV string
     *
     * @param  \Illuminate\Support\Collection|array|string  $data
     * @param  array  $options
     * @return string
     */
    protected function formatCsv($data, array $options)
    {
        if (is_string($data)) {
            $csv = $data;
        } else {
            $csvArray = [];

            if ( $options['include_header']) {
                $this->addHeaderToCsvArray($csvArray, $data, $options);
            }

            $this->addRowsToCsvArray($csvArray, $data, $options);

            $csv = implode("\r\n", $csvArray);
        }

        return mb_convert_encoding($csv, $options['encoding']);
    }

    /**
     * Add a CSV header to an array based on data
     *
     * @param  array  $csvArray
     * @param  \Illuminate\Support\Collection|array  $data
     * @param  array  $options
     * @return void
     */
    protected function addHeaderToCsvArray(array &$csvArray, $data, array $options)
    {
        $firstRowData = $this->getRowData($data[0]);

        if (Arr::isAssoc($firstRowData)) {
            $rowData = array_keys($firstRowData);

            $csvArray[0] = $this->rowDataToCsvString($rowData, $options);
        }
    }

    /**
     * Add CSV rows to an array based on data
     *
     * @param  array  $csvArray
     * @param  \Illuminate\Support\Collection|array  $data
     * @param  array  $options
     * @return void
     */
    protected function addRowsToCsvArray(array &$csvArray, $data, array $options)
    {
        foreach ($data as $row) {
            $rowData = $this->getRowData($row);

            $csvArray[] = $this->rowDataToCsvString($rowData, $options);
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
     * @param  array  $options
     * @return string
     */
    protected function rowDataToCsvString(array $row, array $options)
    {
        if ($options['quoted']) {
            array_walk($row, function (&$cell) {
                $cell = '"' . str_replace('"', '""', $cell) . '"';
            });
        }

        return implode($options['delimiter'], $row);
    }

    /**
     * Get HTTP headers for a CSV response
     *
     * @param  array  $customHeaders
     * @param  array  $options
     * @return void
     */
    protected function createCsvHeaders(array $customHeaders, array $options)
    {
        $baseHeaders = [
            'Content-Type' => 'text/csv; charset=' . $options['encoding'],
            'Content-Encoding' => $options['encoding'],
            'Content-Transfer-Encoding' => 'binary',
            'Content-Description' => 'File Transfer',
        ];

        return array_merge($baseHeaders, $customHeaders);
    }
}
