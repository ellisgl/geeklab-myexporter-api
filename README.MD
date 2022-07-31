# GeekLab MyExporter
PHP Database Exporter that will have some neat features, because PHPMyAdmin is annoying.

## Installation
`$ composer install`

## Docker
`$ docker-compose up --detach`

* WEB: http://localhost:8000
* MySQL localhost:8306

## Notes
* Create a user for tests: test / password
* See [/docs]() for API documentation.
* To create JSON API:
  * JSON: ./vendor/bin/openapi -n *.php -o docs/openapi.json --format json src/
  * YAML: ./vendor/bin/openapi -n *.php -o docs/openapi.yaml --format yaml src/

## Todo
* Better README.
* Full DB exporting.
* Table exporting.
* Email after export.
* Cloud storage integration.
* Email notification of completion or error.
* Multi-threaded queue processing to export multiple tables at once.
* More code comments.
* More tests.

