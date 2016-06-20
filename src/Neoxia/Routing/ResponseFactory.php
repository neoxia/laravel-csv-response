<?php

namespace Neoxia\Routing;

use Illuminate\Routing\ResponseFactory as BaseResponseFactory;

class ResponseFactory extends BaseResponseFactory
{
    public function csv($data, $status = 200, array $headers = [])
    {
        if ($data->isEmpty()) {
            return $this->make('No content', 204);
        }

        $csv = $this->formatCsv($data);
        $headers = $this->createCsvHeaders($headers);

        return $this->make($csv, $status, $headers);
    }

    protected function formatCsv($data)
    {
        $csv = implode(';', array_keys($data->first()->csvSerialize()));

        foreach ($data as $row) {
            $csv .= "\r\n" . implode(';', $row->csvSerialize());
        }

        $csv = mb_convert_encoding($csv, 'WINDOWS-1252');

        return $csv;
    }

    protected function createCsvHeaders($additionalHeaders = [])
    {
        $baseHeaders = [
            'Content-Type' => 'text/csv; charset=WINDOWS-1252',
            'Content-Encoding' => 'WINDOWS-1252',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Description' => 'File Transfer',
        ];

        return array_merge($baseHeaders, $additionalHeaders);
    }
}
