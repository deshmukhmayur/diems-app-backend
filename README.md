# DIEMS College App JSON API Service

The API Service for the College App of [DIEMS, Aurangabad](http://dietms.org).
It provides database handling and other back-end functionality for the Android App
and the upcoming Web App.

## Built with:

- [Slim3](https://www.slimframework.com/) - a micro-framework for PHP
- [Eloquent](https://laravel.com/docs/5.4/eloquent) - an ORM for PHP, developed by Laravel

## Usage

For details on how to use this API and it's endpoints,
refer to the [wiki](https://github.com/deshmukhmayur/diems-app-backend/wiki).

## Installation

### Requirements

The following components should be pre-installed:

- A web server (Recommended: Apache)
- PHP (Recommended: 7.x)
- A Database Engine (Recommended: MariaDB / PostgreSQL)

### Configuration

Clone this Repository:
```bash
$ git clone https://github.com/deshmukhmayur/diems-app-backend.git
```

Install the dependencies:
```bash
$ php composer.phar install
```

After cloning the repository, you need to create a new directory `config/` in the root and add a `settings.php` file in it containing your database and credentials. For eg.
```php
// config/settings.php

$settings = array(
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'database',
    'username' => 'username',
    'password' => 'password',
    'collation' => 'utf8_general_ci',
    'prefix' => '',
);
```

### Running the server:

To run the development server
```bash
$ php -S 0.0.0.0:8880 -t public
```

## Contributing

For details on contributing to this project read the [CONTRIBUTING](CONTRIBUTING.md) file.

## License

This project is licensed under the Apache-2.0.
See the [LICENSE](LICENSE) file for details.
