[![Build Status](https://travis-ci.org/neoxia/laravel-csv-response.svg?branch=master)](https://travis-ci.org/neoxia/laravel-csv-response)
[![Coverage Status](https://coveralls.io/repos/github/neoxia/laravel-csv-response/badge.svg?branch=master)](https://coveralls.io/github/neoxia/laravel-csv-response?branch=master)

## Laravel CSV Response

This package adds a CSV response type to the Laravel ResponseFactory class. Because CSV is a data format, just like JSON, it should be possible to respond to a request with this format.

```PHP
$data = [
    ['first_name', 'last_name'],
    ['John', 'Doe'],
    ['Jane', 'Doe'],
];

return response->csv($data);
```

This small package offers a straightforward solution that deals with conversion, from array or collection of objects to comma separated values string, and character encoding.
