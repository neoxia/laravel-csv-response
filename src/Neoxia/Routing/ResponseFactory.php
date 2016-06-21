<?php

namespace Neoxia\Routing;

use Illuminate\Routing\ResponseFactory as BaseResponseFactory;

class ResponseFactory extends BaseResponseFactory
{
    public function csv($data)
    {
        if ($data->isEmpty()) {
            return $this->make('No Content', 204);
        }

        $csv = $this->formatCsv($data);
        $headers = $this->createCsvHeaders();

        return $this->make($csv, 200, $headers);
    }

    protected function formatCsv($data)
    {
        $csv = implode(';', array_keys($data->first()->csvSerialize()));

        foreach ($data as $row) {
            $csv .= "\r\n" . implode(';', $row->csvSerialize());
        }

        return $csv;
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
