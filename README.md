[![Latest Stable Version](http://img.shields.io/github/release/neoxia/laravel-csv-response.svg)](https://packagist.org/packages/neoxia/laravel-csv-response)
[![Build Status](http://img.shields.io/travis/neoxia/laravel-csv-response.svg)](https://travis-ci.org/neoxia/laravel-csv-response)
[![Coverage Status](http://img.shields.io/coveralls/neoxia/laravel-csv-response.svg)](https://coveralls.io/github/neoxia/laravel-csv-response?branch=master)

## Laravel CSV Response

This package adds a CSV response type to the Laravel `ResponseFactory` class. Because CSV is a data format, just like JSON, it should be possible to respond to a request with this format.

```PHP
$data = [
    ['first_name', 'last_name'],
    ['John', 'Doe'],
    ['Jane', 'Doe'],
];

return response->csv($data);
```

This small package offers a straightforward solution that deals with conversion, from array or collection of objects to comma separated values string, and character encoding.

### Disclaimer

This package is just a pretty cool helper to create CSV responses without pain.

If you want to generate CSV (or Excel) files with a lot of options and more robustness you should take a look at [Maatwebsite/Laravel-Excel](https://github.com/Maatwebsite/Laravel-Excel).

## Installation

In order to install this package, add `neoxia/laravel-csv-response` in `composer.json`.

```JS
"require": {
    "neoxia/laravel-csv-response": "1.1.*"
},
```

And add the service provider in `config/app.php`.

```PHP
Neoxia\Routing\ResponseFactoryServiceProvider::class,
```

## Usage

### Base data format

The `csv()` method is very flexible about data format. All this examples return exactly the same response.

```PHP
response()->csv(collect(
    new User(['first_name' => 'John', 'last_name' => 'Doe']),
    new User(['first_name' => 'Jane', 'last_name' => 'Doe']),
));

response()->csv([
    ['first_name', 'last_name'],
    ['John', 'Doe'],
    ['Jane', 'Doe'],
]);

response()->csv([
    ['first_name' => 'John', 'last_name' => 'Doe'],
    ['first_name' => 'Jane', 'last_name' => 'Doe'],
]);

response()->csv("first_name;last_name\r\nJohn;Doe\r\nJane;Doe");
```

### Objects as rows

If the "rows" of the array of data passed to the method are objects, you have to implement the `csvSerialize()` method in this objects. This method is based in the same principle than the `jsonSerialize()` method that is already implemented into an Eloquent model. It should return data as an associative array.

For example :


```PHP
class User
{
	public function csvSerialize()
	{
		return [
			'first_name' => $this->first_name,
			'last_name' => $this->last_name,
		];
	}
}
```

### CSV first row

When the "rows" of the data collection are associative arrays or objects, the package use the keys of the first row to define the first row of the CSV response. This first row is generaly used as column titles in this type of file.

In order to have a consistent response, you have to be sure that every row in the data collection has the same number of values and the keys in the same order.

### Other parameters

The `csv()` function declaration, based on Laravel `json()` function, is the following.

```PHP
public function csv($data, $status = 200, array $headers = [], array $options = [])
```

#### Status

Typically, you should return your CSV with status *200 Ok* but you are allowed to be imaginative. Maybe are you building a full REST-CSV API ;)

#### Headers

The default headers for this response are the following but you can overwrite it.

```PHP
[
    'Content-Type' => 'text/csv; charset=WINDOWS-1252',
    'Content-Encoding' => 'WINDOWS-1252',
    'Content-Transfer-Encoding' => 'binary',
    'Content-Description' => 'File Transfer',
]
```

Note that the default charset and encoding are automatically overwrited if a custom encoding is specified in the options (see below).

#### Options

The last argument lets you define how the CSV is built and formated. We have defined a format that fits very well in most cases but the optimal configuration may depend on your environment (language, Microsoft Office version, etc.).

The default options are the following.

```PHP
[
    'encoding' => 'WINDOWS-1252',
    'delimiter' => ';',
    'quoted' => true,
]
```
